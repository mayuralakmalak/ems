<?php 
error_reporting(0);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Search Result</title>
</head>

<body>

	<table cellspacing="2" cellpadding="1" style="border:1px solid grey;" valign="top" align="center" width="50%">
	<tbody>	<form name="f1" action="" method="post">

		<tr>
			
			<td align="center">Search ::</td>

		</tr>

		<tr>
			<td align="center">Keyword &nbsp;&nbsp;&nbsp; : &nbsp;&nbsp;&nbsp; <input type="text" name="keyword" value="<?php echo $_POST['keyword']; ?>"></td>
		</tr>

		<tr>
			<td align="center">path &nbsp;&nbsp;&nbsp; : &nbsp;&nbsp;&nbsp; <input type="text" name="path" value="<?php echo $_POST['path']; ?>"></td>
		</tr>
	
		<tr>
			
			<td align="center">
			<input type="submit" name="submit" value="search">
			</td>
			
		</tr>

	</tbody>
	</table>

<br /><br /><br />

<?php

echo $projectpath = $_SERVER['DOCUMENT_ROOT']."/".$_POST['path'];

function getDirList($dirName) 
{
	global $dirlist;
    $dirlist[] = $dirName;
    $d = dir($dirName);
	
    if($d) 
	{
      while($entry = $d->read()) 
	  {
        if($entry != "." && $entry != "..") 
		{
          if(is_dir($dirName."/".$entry)) 
		  {
              getDirList($dirName."/".$entry);

          } 
        }
      }
      $d->close();
    }
	
    return $dirlist;
}

function seek_n_display($include_root = false) 
{
 	global $formatteddirlist, $found, $keyword, $filelist;
	
    for ($i = 0, $n = sizeof($formatteddirlist); $i < $n; $i++) 
	{
      $dir_check = $formatteddirlist[$i];

      if($dir = dir($dir_check)) 
	  {
        while ($file = $dir->read()) 
		{
          if (!is_dir($dir_check . $file)) 
		  {
              $filelist[] = $dir_check . $file;
          }
        }
        if (sizeof($filelist)) 
		{
          sort($filelist);
        }
        $dir->close();
      }
    }

    echo '<table border="0" width="100%" cellspacing="2" cellpadding="1" align="center">' . "\n";
	echo '<tr ><td >' . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . "Searching " . sizeof($filelist) . " files for " .  "<b><i>" . $keyword . '</i></b></td></tr></table>' . "\n\n";
    echo '<tr><td>&nbsp;</td></tr>';

    $file_cnt = 0;
    $cnt_found=0;
    
	for ($i = 0, $n = sizeof($filelist); $i < $n; $i++) 
	{
      $file_cnt++;
      $file = $filelist[$i];

      while(strstr($file, '//')) $file = str_replace('//', '/', $file);

      $show_file = '';
      if (file_exists($file)) 
	  {
        $show_file .= "\n" . '<table border="2" width="95%" cellspacing="2" cellpadding="1" align="center"><tr><td >' . "\n";
        $show_file .= '<tr><td >';
        $show_file .= '<strong>' . $file . '</strong>';
        $show_file .= '</td></tr>';
        $show_file .= '<tr><td>';

        $lines = file($file);
        $found_line = 'false';

        foreach ($lines as $line_num => $line) 
		{
          $cnt_lines++;
          if (strstr(strtoupper($line), strtoupper($keyword))) 
		  {
            $found_line= 'true';
            $found = 'true';
            $cnt_found++;
            $show_file .= "<br />Line #<strong>{$line_num}</strong> : " ;

			$show_file .= htmlspecialchars($line);

			$show_file = str_replace($keyword,"<span style='background:#FAAFBE;'>".$keyword."</span>",$show_file);
	
            $show_file .= "<br />\n";
          } 
		  else 
		  {
            if($cnt_lines >= 5) 
			{
              $cnt_lines=0;
            }
          }
        }
      }
      $show_file .= '</td></tr></table>' . "\n";

      if ($found_line == 'true') 
	  {
        echo $show_file . '<table><tr><td>&nbsp;</td></tr></table>';
      } 
    }
    echo '<table border="0" width="100%" cellspacing="2" cellpadding="1" align="center"><tr><td>' . "Number of matches found " . $cnt_found . '</td></tr></table>';
  } 


//get current dir and use it.
//$keyword = "zen_href_link";

$_POST['keyword']=isset($_POST['keyword']) ? $_POST['keyword'] : '';

$keyword = $_POST['keyword'];

	if($keyword != '')
	{
		getDirList($projectpath);
	
//print_r($dirlist);

echo "<br><Br><br><br>";

$check_dir = $dirlist;
for ($i = 0, $n = sizeof($check_dir); $i < $n; $i++) 
{
	$formatteddirlist[] = $check_dir[$i] . '/';
}

seek_n_display();
 	}
?>

</body>
</html>
