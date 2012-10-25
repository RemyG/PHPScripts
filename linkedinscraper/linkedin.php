<?php

/**
 * Website: http://sourceforge.net/projects/simplehtmldom/
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author Remy Gardette (http://remyg.fr)
 * @version 0.1
 */

require_once 'libs/simple_html_dom.php';
/**
 * Scrapes a resume on LinkedIn and create an XML file with the result.
 *
 * @param 	string 	url of the resume to scrape
 * @param 	string 	location of the resulting file (can already exist, in which case it will be overwritten, or not)
 *
 * @return 	-
 */
function scrapeResume($resume_url, $dest_file) {
	
	$html = file_get_html($resume_url);
	
	$fh = fopen($dest_file, 'w');
	
	fwrite($fh, '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL);
	fwrite($fh, '<resume>'.PHP_EOL);
	
	$pos_nb = 0;
	
	foreach($html->find('div[class*=position]') as $element) {
	
		if($element->find('div/h3/span[class=title]') != null) {
	
			$pos_nb += 1;
			fwrite($fh, '<position>'.PHP_EOL);
	
			fwrite($fh, '<title>'.PHP_EOL);
			fwrite($fh, $element->find('div/h3/span[class=title]', 0)->plaintext.PHP_EOL);
			fwrite($fh, '</title>'.PHP_EOL);
	
			if($element->find('div/h4//span') != null) {
				fwrite($fh, '<company>'.PHP_EOL);
				fwrite($fh, $element->find('div/h4//span', 0)->plaintext.PHP_EOL);
				fwrite($fh, '</company>'.PHP_EOL);
			}
	
			if($element->find('p[class=period]/span[class=location]') != null) {
				fwrite($fh, '<location>'.PHP_EOL);
				fwrite($fh, $element->find('p[class=period]/span[class=location]', 0)->plaintext.PHP_EOL);
				fwrite($fh, '</location>'.PHP_EOL);
			}
	
			if($element->find('p[class=period]/abbr') != null) {
				fwrite($fh, '<from>'.PHP_EOL);
				fwrite($fh, $element->find('p[class=period]/abbr', 0)->plaintext.PHP_EOL);
				fwrite($fh, '</from>'.PHP_EOL);
			}
	
			if($element->find('p[@class=period]/abbr') != null) {
				fwrite($fh, '<to>'.PHP_EOL);
				fwrite($fh, $element->find('p[class=period]/abbr', 1)->plaintext.PHP_EOL);
				fwrite($fh, '</to>'.PHP_EOL);
			}
	
			if($element->find('p[class*=description]') != null) {
				fwrite($fh, '<description>'.PHP_EOL);
				$desc = $element->find('p[class*=description]', 0);
				$desc = substr($desc, strpos($desc, ">") + 1);
				$desc = substr($desc, 0, strrpos($desc, "<"));
				$desc = trim($desc);
				$desc = htmlspecialchars($desc);
				fwrite($fh, $desc.PHP_EOL);
				fwrite($fh, '</description>'.PHP_EOL);
			}
	
			fwrite($fh, '</position>'.PHP_EOL);
	
		}
	
	}
	
	fwrite($fh, '</resume>'.PHP_EOL);
	
}

scrapeResume('http://fr.linkedin.com/in/remygardette', '../data/linkedin_resume_en.xml');
scrapeResume('http://fr.linkedin.com/in/remygardette/fr', '../data/linkedin_resume_fr.xml');

