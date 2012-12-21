<?php

backup_tables('hostname', 'username', 'password', 'dbname');


/* backup the db OR just a table */
function backup_tables($host, $user, $pass, $name, $tables = '*')
{
  
  $mysqli = new mysqli($host, $user, $pass, $name);
  
  /* check connection */
  if ($mysqli->connect_errno) {
    printf('Connect failed: %s\n', $mysqli->connect_error);
    exit();
  }

  $mysqli->set_charset('utf8');

  //get all of the tables
  if($tables == '*')
  {
    $tables = array();
    if($result = $mysqli->query('SHOW TABLES')) {
        while($row = $result->fetch_row()) {
            $tables[] = $row[0];
        }
        $result->close();
    } else {
        printf('Query Error: %s\n', $mysqli->error);
    }
  }
  else
  {
    $tables = is_array($tables) ? $tables : explode(',',$tables);
  }
  
  //cycle through
  foreach($tables as $table)
  {
    if($result = $mysqli->query('SELECT * FROM '.$table)) {

        $num_fields = mysqli_num_fields($result);
        
        if($result2 = $mysqli->query('SHOW CREATE TABLE '.$table)) {
            $return.= 'DROP TABLE '.$table.';';
            $row = $result2->fetch_row();
            $return.= '\n\n'.$row[1].';\n\n';
            $result2->close();
        } else {
            continue;
        }

        for ($i = 0; $i < $num_fields; $i++) 
        {
          while($row = $result->fetch_row())
          {
            $return.= 'INSERT INTO '.$table.' VALUES(';
            for($j=0; $j < $num_fields; $j++) 
            {
              $row[$j] = addslashes($row[$j]);
              $row[$j] = preg_replace('/\n/', '\\n', $row[$j]);
              if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
              if ($j<($num_fields-1)) { $return.= ','; }
            }
            $return.= ');\n';
          }
        }

    } else {
        printf('Query Error: %s\n', $mysqli->error);
    }
    $return .= '\n\n\n';
  }
  
  //save file
  $fileName = 'db-backup-'.date('Ymd-His');
  $fileNameSql = $fileName.'.sql';
  $handle = fopen($fileNameSql, 'w+');
  fwrite($handle,$return);
  fclose($handle);
  $command = 'tar -cvzf '.$fileName.'.tar.gz '.$fileNameSql;
  system($command);
  unlink($fileNameSql);
}

?>
