<?php
ini_set('memory_limit', '-1');
ini_set('display_errors', 0);
set_time_limit(0);
error_reporting(0);

function marime_fisier($bytes)
{
    $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            ),
        );

    foreach($arBytes as $arItem)
    {
        if($bytes >= $arItem["VALUE"])
        {
            $result = $bytes / $arItem["VALUE"];
            $result = str_replace(".", "," , strval(round($result, 2)))." ".$arItem["UNIT"];
            break;
        }
    }
	if(isset($result))
		return $result!=0 ? $result : "0 B";
}

function download($file, $delete){
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename='.$file);
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	header('Content-Length: '.filesize($file));
	readfile($file);
	
	if($delete == 1)
		unlink($file);
	
	exit();
}

function creare_director(){
	if($_GET['actiune'] == 'creare_dir'){
		//Schimbam directorul
		chdir(getcwd().'/'.urldecode($_GET['director']));
				
		//Verificam daca fisierul nu exista
		if(file_exists($_POST['denumire']))
			die('Acest director exista deja!');
		else //Verificam daca fisierul nu s-a putut crea
			if(!mkdir($_POST['denumire'], 0777))
				die('Nu am putut crea acest director!');
			
		//Preluam locatia anterioara
		$redir = urldecode($_GET['locatie']);
							
		//Redirectionare
		echo '<script> location.replace("http://'. $_SERVER['SERVER_NAME'].$redir .'"); </script>';
	}
}

function creare_fisier(){
	if($_GET['actiune'] == 'creare_fis'){
		//Schimbam directorul
		chdir(getcwd().'/'.urldecode($_GET['director']));
			
		//Daca nu s-a completat campul "extensie" atribuim automat extensia ".txt"
		if(empty($_POST['extensie']))
			$_POST['extensie'] = 'txt';
				
		$denumire = $_POST['denumire'].'.'.$_POST['extensie'];
								
		//Verificam daca fisierul nu exista
		if(file_exists($_POST['denumire']) && is_file($_POST['denumire']))
			die('Acest fisier exista deja!');
		else //Verificam daca fisierul nu s-a putut crea
			if(!fopen("$denumire", 'w'))
				die('Nu am putut crea acest fisier!');
					
		//Preluam locatia anterioara
		$redir = urldecode($_GET['locatie']);
							
		//Redirectionare
		echo '<script> location.replace("http://'. $_SERVER['SERVER_NAME'].$redir .'"); </script>';
	}
}

function arhivare(){
	if($_GET['actiune'] == 'arhivare' && isset($_GET['nume'])){
		//Schimbam directorul
		chdir(getcwd().'/'.urldecode($_GET['director']));
				
		//Preluam locatia de pe server a fisierului
		$the_folder = $_GET['nume'];
				
		// echo getcwd().;
		$zip_file_name = 'arh_'.rand(0,100).rand(0,20). chr(rand(65,90)).rand(0,30). chr(rand(65,90)).".zip";

		class FlxZipArchive extends ZipArchive {
			/** Add a Dir with Files and Subdirs to the archive;;;;; @param string $location Real Location;;;;  @param string $name Name in Archive;;; @author Nicolas Heimann;;;; @access private  **/
			public function addDir($location, $name) {
				$this->addEmptyDir($name);
				 $this->addDirDo($location, $name);
			 } // EO addDir;

				/**  Add Files & Dirs to archive;;;; @param string $location Real Location;  @param string $name Name in Archive;;;;;; @author Nicolas Heimann * @access private   **/
			private function addDirDo($location, $name) {
				$name .= "/";      
				$location .= "/";
				
				// Read all Files in Dir
				$dir = opendir ($location);
				while ($file = readdir($dir))    {
					if ($file == "." || $file == "..") 
						continue;
					// Rekursiv, If dir: FlxZipArchive::addDir(), else ::File();
					$do = (filetype( $location . $file) == "dir") ? "addDir" : "addFile";
					$this->$do($location . $file, $name . $file);
				}
			} 
		}

		$za = new FlxZipArchive;
		$res = $za->open($zip_file_name, ZipArchive::CREATE);
		if($res === TRUE)    {
			$za->addDir($the_folder, basename($the_folder)); $za->close();
			
			//Preluam locatia anterioara
			$redir = urldecode($_GET['locatie']);
					
			//Redirectionare
			echo '<script>location.replace("http://'. $_SERVER['SERVER_NAME'].$redir .'"); </script>';
			
		}
		else  { echo "Nu am putut arhiva acest fisier!";}
	}
}

function vizualizare(){
	if($_GET['actiune'] == 'vizualizare' && isset($_GET['nume'])){
		//Preluam locatia de pe server a fisierului
		$locatie = getcwd().'/'.$_GET['director'].'/'.urldecode($_GET['nume']);
		echo '<code><pre>'. htmlentities(highlight_string(file_get_contents($locatie))) .'</pre></code>';		
	}
}

function modificare(){
	if($_GET['actiune'] == 'modificare' && isset($_GET['nume'])){
		//Preluam locatia de pe server a fisierului
		$locatie = getcwd().'/'.$_GET['director'].'/'.urldecode($_GET['nume']);
				
		//Daca s-a trimis formularul
		if(isset($_POST['modificare'])){				
			//Modificam continutul fisierului
			file_put_contents($locatie, $_POST['cod']);
					
			//Preluam locatia anterioara
			$redir = urldecode($_GET['locatie']);
					
			//Redirectionare
			echo '<script> location.replace("http://'. $_SERVER['SERVER_NAME'].$redir .'"); </script>';
		}
		echo '
			<h1><b>'. $_GET['nume'] .'</b></h1>
			<div class="modal-body" style="position:relative; top:10%;">
				<form action="" method="POST">
					<textarea  class="form-control" rows="20" name="cod">'. htmlentities(file_get_contents($locatie)) .'</textarea>
					<br>
					<button type="submit" name="modificare" class="btn btn-success">Salveaza</button>
				</form>
			</div>
		';
	}
			
	
}

function stergere(){
	if($_GET['actiune'] == 'sterge' && isset($_GET['nume'])){
		//Preluam locatia de pe server a fisierului
		$locatie = getcwd().'/'.$_GET['director'].'/'.urldecode($_GET['nume']);
		
		//Schimbam directorul
		chdir(getcwd().'/'.urldecode($_GET['director']));
				
		//Stergem fisierul/directorul
		if(is_file($_GET['nume']))
			unlink($locatie);
		else{
			$it = new RecursiveDirectoryIterator($locatie, FilesystemIterator::SKIP_DOTS);
			$it = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
			foreach($it as $file) {
				if ($file->isDir()) rmdir($file->getPathname());
				else unlink($file->getPathname());
			}
			rmdir($locatie);
		}
			
		//Preluam locatia anterioara
		$redir = urldecode($_GET['locatie']);
				
		//Redirectionare
		echo '<script> location.replace("http://'. $_SERVER['SERVER_NAME'].$redir .'"); </script>';
	}
}

function all_db(){
	$con = $con = mysqli_connect(''.urldecode($_GET['host']).'', ''.urldecode($_GET['utilizator']).'', ''.urldecode($_GET['parola']).'', ''.urldecode($_GET['nume_db']).'');
	$res = mysqli_query($con, "SHOW DATABASES");
	
	$exclude_db = array('information_schema', 'mysql', 'performance_schema');
	echo "<ul>";
	while ($row = mysqli_fetch_assoc($res)) {
		if(!in_array($row['Database'], $exclude_db)){
			echo "<li>". $row['Database'] . "</li>\n";
		}
	}
	echo "</ul>";
}

function export($host, $user, $name, $pass, $tables){
	$data = "\n/*---------------------------------------------------------------".
			"\n  SQL DB BACKUP ".date("d.m.Y H:i")." ".
			"\n  HOST: {$host}".
			"\n  BAZA DE DATE: {$name}".
			"\n  TABEL: {$tables}".
			"\n  ---------------------------------------------------------------*/\n";
  	$con = mysqli_connect(''.urldecode($_GET['host']).'', ''.urldecode($_GET['utilizator']).'', ''.urldecode($_GET['parola']).'', ''.urldecode($_GET['nume_db']).'');

	mysqli_select_db($con, $name);
	mysqli_query($con, "SET NAMES `utf8` COLLATE `utf8_general_ci`"); // Unicode

	if($tables == '*'){ //get all of the tables
		$tables = array();
		$result = mysqli_query($con, "SHOW TABLES");
		while($row = mysqli_fetch_row($result))
			$tables[] = $row[0];
	} else 
		$tables = is_array($tables) ? $tables : explode(',',$tables);
	
	foreach($tables as $table){
		$data.= "\n/*---------------------------------------------------------------".
				"\n  TABEL: `{$table}`".
				"\n  ---------------------------------------------------------------*/\n";           
		$data.= "DROP TABLE IF EXISTS `{$table}`;\n";
		$res = mysqli_query($con, "SHOW CREATE TABLE `{$table}`");
		$row = mysqli_fetch_row($res);
		$data.= $row[1].";\n";

		$result = mysqli_query($con, "SELECT * FROM `{$table}`");
		$num_rows = mysqli_num_rows($result);    

		if($num_rows>0){
			$vals = Array(); $z=0;
			for($i=0; $i<$num_rows; $i++){
				$items = mysqli_fetch_row($result);
				$vals[$z]="(";
				for($j=0; $j<count($items); $j++){
					if (isset($items[$j]))
						$vals[$z].= "'".mysqli_real_escape_string($con, $items[$j])."'";  
					else
						$vals[$z].= "NULL";
					
					if ($j<(count($items)-1)) 
						$vals[$z].= ","; 
				}
				
				$vals[$z].= ")";
				$z++;
		  }
		  $data.= "INSERT INTO `{$table}` VALUES ";      
		  $data .= "  ".implode(";\nINSERT INTO `{$table}` VALUES ", $vals).";\n";
		}
	}
	return $data;
}

if(isset($_GET['export']) && $_GET['export'] == "normal" && isset($_GET['nume_tbl'])){
	if($_GET['nume_tbl'] == '*')
		$backup_file = 'db-backup-'.time().'.sql';
	else
		$backup_file = $_GET['nume_tbl'].'-'.time().'.sql';
		

	// get backup
	$mybackup = export(urldecode($_GET['host']), urldecode($_GET['nume_db']), urldecode($_GET['utilizator']), urldecode($_GET['parola']), urldecode($_GET['nume_tbl']));

	// save to file
	$handle = fopen($backup_file,'w+');
	fwrite($handle,$mybackup);
	fclose($handle);
	
	download($backup_file, 1);	
}

if(isset($_GET['export']) && $_GET['export'] == "csv" && isset($_GET['nume_tbl'])){
	$backup_file = $_GET['nume_tbl'].'-'.time().'.csv';
		

	$con = mysqli_connect(''.urldecode($_GET['host']).'', ''.urldecode($_GET['utilizator']).'', ''.urldecode($_GET['parola']).'', ''.urldecode($_GET['nume_db']).'');
	
	function mysqli_field_name($result, $field_offset)
	{
		$properties = mysqli_fetch_field_direct($result, $field_offset);
		return is_object($properties) ? $properties->name : null;
	}

	$query = "SELECT * FROM `". $_GET['nume_tbl'] ."`";
	$result = mysqli_query($con, $query);

	$number_of_fields = mysqli_num_fields($result);
	
	
	$headers = array();
	for ($i = 0; $i < $number_of_fields; $i++) {
		$headers[] = mysqli_field_name($result , $i);
	}
	
	// save to file
	$fp = fopen("php://output", 'w+');
	if ($fp && $result) {
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="'. $backup_file .'"');
		header('Pragma: no-cache');
		header('Expires: 0');
		
		fputcsv($fp, $headers);
		while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
			fputcsv($fp, array_values($row));
		}

		fclose($fp);
		die;
	}
}

if(isset($_GET['actiune']) && $_GET['actiune'] == 'descarcare' && isset($_GET['nume'])){
	chdir(getcwd().'/'.urldecode($_GET['director']));
	
	if (file_exists(strip_tags($_GET["nume"])))
		download($_GET["nume"], 0);
	else
		die('Fisierul nu exista!');
}
?>
<!DOCTYPE html>
<html>
	<head>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
		<script defer src="https://use.fontawesome.com/releases/v5.0.10/js/all.js" integrity="sha384-slN8GvtUJGnv6ca26v8EzVaR9DC58QEwsIk9q1QXdCU8Yu8ck/tL/5szYlBbqmS+" crossorigin="anonymous"></script>
		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
	</head>

	<body class="container">
		<?php
		//Verificam daca link-ul este corect
		if(isset($_GET['actiune']) && !empty($_GET['actiune'])){
			$director = (isset($_GET['director']) ? $_GET['director'] : ".");
			
			//Daca parametrul 'director' este gol afisam eroare
			if(empty($director))
				die('Nu exista un director');
			
			if($_GET['actiune'] == 'listare'){
		?>
			
			<div class="row" style="padding-top: 1%; padding-bottom: 2%; text-align: right;">
				<div class="col-md-8">
					<pre>
					In lucru locatie actuala
					</pre>
				</div>
				<div class="col-md-4">
					<button class="btn btn-success" data-toggle="modal" data-target="#Folder">Folder nou</button>
					
					<div class="modal fade" id="Folder">
						<div class="modal-dialog">
							<div class="modal-content">
								<!-- Modal Header -->
								<div class="modal-header">
								  <h4 class="modal-title">Creare director</h4>
								  <button type="button" class="close" data-dismiss="modal">&times;</button>
								</div>
								
								<!-- Modal body -->
								<form action="?actiune=creare_dir&director=<?php echo urlencode($director); ?>&locatie=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" method="POST">
									<div class="modal-body">
										<input type="text" name="denumire" class="form-control" placeholder="Denumire" />
									</div>
									
									<!-- Modal footer -->
									<div class="modal-footer">
									  <button type="button" class="btn btn-danger" data-dismiss="modal">Iesire</button>
									  <button type="submit" name="crdir" class="btn btn-success">Creare</button>
									</div>
								</form>
							</div>
						</div>
					</div>
					
					<button class="btn btn-info" data-toggle="modal" data-target="#fisier">Fisier nou</button>
					
					<div class="modal fade" id="fisier">
						<div class="modal-dialog">
							<div class="modal-content">
								<!-- Modal Header -->
								<div class="modal-header">
								  <h4 class="modal-title">Creare fisier</h4>
								  <button type="button" class="close" data-dismiss="modal">&times;</button>
								</div>
								
								<!-- Modal body -->
								<form action="?actiune=creare_fis&director=<?php echo urlencode($director); ?>&locatie=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" method="POST">
									<div class="modal-body">
										<div class="form-group">
											<input type="text" name="denumire" class="form-control" placeholder="Denumire" />
										</div>
										<div class="form-group">
											<input type="text" name="extensie" class="form-control" placeholder="Extensie (implicit .txt)" />
										</div>
									</div>
									
									<!-- Modal footer -->
									<div class="modal-footer">
									  <button type="button" class="btn btn-danger" data-dismiss="modal">Iesire</button>
									  <button type="submit" name="crdir" class="btn btn-success">Creare</button>
									</div>
								</form>
							</div>
						</div>
					</div>					
				</div>
			</div>
			<table class="table table-hover">
					<tr>
						<th>
							<a href="http://
							<?php 
							echo $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
							
							if($director == '.'){
								echo '&director=';
								echo urlencode('./../');
							} else
								echo urlencode('/..');
							?>"><i class="fa fa-arrow-left"></i> Inapoi</a>
						</th>	
						<th>Dimensiune</th>	
						<th>*</th>	
					</tr>
					<?php
					//Verificam daca fisierul este un director
					if(is_dir($director)){
						
						//Preluam fisierele fara "." si ".."
						$fisiere = array_diff(scandir($director), array('.', ".."));

						//Schimbam directorul
						if(isset($_GET['director']))
							chdir(getcwd().'/'.urldecode($_GET['director']));
							
						//Aranjam directoarele primele
						usort($fisiere, function($a, $b) {	
							if(is_dir($a) == is_dir($b))
								return strnatcasecmp($a, $b);
							else
								return is_dir($a) ? -1 : 1;
						});
						
						//Afisam fisierele/directoarele
						$i=1; // Indice pentru fisiere
						$j=0; // Indice pentru directoare
						foreach($fisiere as $denumire){
							//Preluam locatia fisierului/folderului
							$locatie = getcwd().'/'.$denumire;
							$_SESSION['locatie'] = $locatie;
					?>
					<tr>
						<th>
							<?php
								if(is_dir($locatie))
									echo '<i class="fa fa-folder fa-fw"></i> <a href="?actiune=listare&director='. urlencode("{$director}/{$denumire}") . '">'. $denumire .'</a>';
								else if(is_file($locatie))
									echo '<i class="fa fa-code fa-fw"></i> '.$denumire;
								else
									echo 'eroare #1';
							?>
						</th>
						
						<th>
							<?php 
							echo is_file($locatie) ? marime_fisier(filesize($denumire)) : '-';
							?>
						</th>
						
						<?php if(is_file($locatie)){ ?>
						<td>
							<div class="row">
								<?php
								//Verificam daca fisierul este o arhiva
								$info = pathinfo($locatie);
								if (isset($info['extension']) && $info["extension"] == "zip"){							
								?>
									&nbsp
									<a href="<?php echo str_replace("\\",'/',"http://".$_SERVER['HTTP_HOST'].substr(getcwd(),strlen($_SERVER['DOCUMENT_ROOT']))).'/'.$denumire; ?>"><button class="btn btn-success"><i class="fa fa-download"></i></button></a>
								<?php } else { ?>
									
									<a href="?actiune=vizualizare&nume=<?php echo $denumire; ?>&director=<?php echo urlencode($director); ?>"><button class="btn btn-info"><i class="fa fa-eye"></i></button></a>
									
									&nbsp
									<a href="?actiune=modificare&nume=<?php echo $denumire; ?>&director=<?php echo urlencode($director); ?>&locatie=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"><button class="btn btn-primary"><i class="fa fa-edit"></i></button></a>
									
									&nbsp
									<a href="?actiune=descarcare&nume=<?php echo $denumire; ?>&director=<?php echo urlencode($director); ?>"><button class="btn btn-success"><i class="fa fa-download"></i></button></a>
								<?php } ?>
								
								<!-- Stergere fisier -->
								&nbsp
								<button class="btn btn-danger" data-toggle="modal" data-target="#sterge<?php echo $i; ?>"><i class="fa fa-trash"></i></button>
							
								<div class="modal fade" id="sterge<?php echo $i; ?>">
									<div class="modal-dialog" role="document">
										<div class="modal-content">
											<div class="modal-header">
												<h5 class="modal-title" id="exampleModalLabel">Stergere fisier</h5>
												<button type="button" class="close" data-dismiss="modal" aria-label="Close">
													<span aria-hidden="true">&times;</span>
												</button>
											</div>
											<div class="modal-body">
												Sunteti sigur ca vreti sa stergeti fisierul <b><i><?php echo $denumire; ?></i></b> ?
											</div>
											<div class="modal-footer">
												<button type="button" class="btn btn-secondary" data-dismiss="modal">Inchide</button>
												<a href="?actiune=sterge&nume=<?php echo urlencode($denumire); ?>&director=<?php echo urlencode($director); ?>&locatie=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"><button type="button" class="btn btn-danger">Sterge</button></a>
											</div>
										</div>
									</div>
								</div>
								<!-- Sfarsit stergere fisier -->
							</div>
						</td>
						<?php $i++; } else if(is_dir($locatie)){?>
						<td>
							<div class="row">
								
								<a href="?actiune=arhivare&nume=<?php echo $denumire; ?>&director=<?php echo urlencode($director); ?>&locatie=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"><button class="btn btn-primary"><i class="fa fa-file-archive"></i></button></a>
								
								<!-- Stergere director -->
								&nbsp
								<button class="btn btn-danger" data-toggle="modal" data-target="#sterge<?php echo $j; ?>"><i class="fa fa-trash"></i></button>
								<div class="modal fade" id="sterge<?php echo $j; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
									<div class="modal-dialog" role="document">
										<div class="modal-content">
											<div class="modal-header">
												<h5 class="modal-title" id="exampleModalLabel">Stergere director</h5>
												<button type="button" class="close" data-dismiss="modal" aria-label="Close">
													<span aria-hidden="true">&times;</span>
												</button>
											</div>
											<div class="modal-body">
												Sunteti sigur ca vreti sa stergeti directorul <b><i><?php echo $denumire; ?></i></b> ?
											</div>
											<div class="modal-footer">
												<button type="button" class="btn btn-secondary" data-dismiss="modal">Inchide</button>
												<a href="?actiune=sterge&nume=<?php echo urlencode($denumire); ?>&director=<?php echo urlencode($director); ?>&locatie=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>"><button type="button" class="btn btn-danger">Sterge</button></a>
											</div>
										</div>
									</div>
								</div>
								<!-- Sfarsit stergere director-->
							</div>
						</td>
						<?php $j--; } else echo '<td>err act</td>';?>
					</tr>		
				<?php
						} // sfarsit foreach
					} // sfarsit verificare fisier -> director
				?>
			</table>
			
		<?php
			} // sfarsit verificare actiune==listare
			
			creare_director();
			creare_fisier();
			arhivare();
			vizualizare();
			modificare();
			stergere();
			
			////////////////////////////////
			//							  //
			//   ACTIUNE = BAZA DE DATE	  //
			//							  //
			////////////////////////////////
			
			//Daca informatiile bazei de date nu s-au completat afisam formularul
			if($_GET['actiune'] == 'db' && !isset($_GET['nume_db']) && !isset($_GET['utilizator'])&& !isset($_GET['parola'])){
				//Daca s-au trimis datele redirectionam
				if(isset($_POST['submit']))
					echo '
					<script>
					window.location.replace("?actiune=db&host='.urlencode($_POST['host']).'&nume_db='.urlencode($_POST['nume_db']).'&utilizator='.urlencode($_POST['utilizator']).'&parola='.urlencode($_POST['parola']).'");
					</script>
				';
			?>
			<form method="POST" action="">
				<div class="form-group col-md-6">
					Host
					<input type="text" required class="form-control" name="host" value="localhost"/>
				</div>
				<div class="form-group col-md-6">
					Nume baza de date
					<input type="text" required class="form-control" name="nume_db" placeholder="Nume DB"/>
				</div>
				<div class="form-group col-md-6">
					Utilizator baza de date
					<input type="text" required class="form-control" name="utilizator" placeholder="Utilizator DB"/>
				</div>
				<div class="form-group col-md-6">
					Parola baza de date
					<input type="text" class="form-control" name="parola" placeholder="Parola DB"/>
				</div>
				<div class="form-group col-md-6">
					<button type="submit" class="btn btn-info" name="submit">Trimite</button>
				</div>
			</form>
			<?php
			//Daca s-au completat datele
			} else if($_GET['actiune'] == 'db' && isset($_GET['host']) && isset($_GET['nume_db']) && isset($_GET['utilizator'])&& isset($_GET['parola'])){
				$con = mysqli_connect(''.urldecode($_GET['host']).'', ''.urldecode($_GET['utilizator']).'', ''.urldecode($_GET['parola']).'', ''.urldecode($_GET['nume_db']).'');
				
				//Afisam eroare daca nu s-a putut conecta
				if (!$con) {
					echo "Eroare: Nu a fost posibila conectarea la MySQL." . PHP_EOL .'<br>';
					echo "Valoarea errno: " . mysqli_connect_errno() . PHP_EOL .'<br>';
					echo "Valoarea error: " . mysqli_connect_error() . PHP_EOL .'<br>';
					exit;
				} else {
			
			if(!isset($_GET['nume_tabel'])) {
			?>
			<br>
			<div class="col-md-12">
				<div class="row">
					<div class="col-md-1">
						<!-- Afisam numele tuturor bazelor de date -->
						<button class="btn btn-info" data-toggle="modal" data-target="#db">DB *</button>
							
						<div class="modal fade" id="db">
							<div class="modal-dialog" role="document">
								<div class="modal-content">
									<div class="modal-header">
										<h5 class="modal-title" id="exampleModalLabel">Toate bazele de date</h5>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>
									<div class="modal-body">
										<?php all_db(); ?>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-secondary" data-dismiss="modal">Inchide</button>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-10"></div>
					<div class="col-md-1">
						<a href="">
							<a href="<?php echo $_SERVER['REQUEST_URI']?>&export=normal&nume_tbl=*"><button class="btn btn-info">Export</button></a>
						</a>
					</div>
				</div>
			</div>
			<br><br>
			<?php } ?>
			<table class="table">
				<thead>
					<tr>
						
						<?php if(!isset($_GET['nume_tabel'])) {?>
							<th scope="col">#</th>
							<th scope="col">Denumire</th>
							<th scope="col">Nr. randuri</th>
							<th scope="col">Actiune</th>
						<?php } ?> 
					</tr>
				</thead>
				<tbody>
				<?php
				//Daca nu s-a definit 'nume_tabel'
				if(!isset($_GET['nume_tabel'])){
					//Selectam toate tabelele din baza de date
					$sql  = mysqli_query($con, "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '". $_GET['nume_db'] ."'");
					
					//Contor pentru fiecare tabel
					$i=1;
					while($rand = mysqli_fetch_assoc($sql)){
				?>
						<tr>
							<th scope="row"><?php echo $i++; ?></th>
							<td>
								<a href="<?php echo $_SERVER['REQUEST_URI'].'&nume_tabel='.$rand['TABLE_NAME']; ?>&p=1"><?php echo $rand['TABLE_NAME']; ?></a>
							</td>
							<td>
								<?php echo mysqli_num_rows(mysqli_query($con, "SELECT * FROM `". $rand['TABLE_NAME'] ."`")); ?>
							</td>
							<td>
								<div class="row">
									<a href="<?php echo $_SERVER['REQUEST_URI']?>&export=normal&nume_tbl=<?php echo $rand['TABLE_NAME']; ?>"><button class="btn btn-success"><i class="fa fa-download"></i> SQL</button></a>
									&nbsp
									<a href="<?php echo $_SERVER['REQUEST_URI']?>&export=csv&nume_tbl=<?php echo $rand['TABLE_NAME']; ?>"><button class="btn btn-success"><i class="fa fa-download"></i> CSV</button></a>
								</div>
							</td>
						</tr>
					<?php
					}
					echo '
							</tbody>
						</table>';
			
				} else { // Daca s-a definit 'nume_tabel', afisam informatile din baza de date
					if(!isset($_GET['editare']) && !isset($_GET['query'])){
						$sql = mysqli_query($con, "SELECT * FROM `". $_GET['nume_tabel'] ."`");
						
						if(mysqli_num_rows($sql))
							$afisare = 1;
						else
							$afisare = 0;
							
						//Daca s-a gasit cel putin un rand afisam butonul de editare
						if($afisare){
							echo '
								<br>
								<div class="col-md-12">
									<div class="row">
										<div class="col-md-1">
											<a href="">
												<a href="'. $_SERVER['REQUEST_URI'].'&editare=da"><button class="btn btn-info">Editare camp</button></a>
											</a>
										</div>
										<div class="col-md-10"></div>
										<div class="col-md-1">
											<a href="">
												<a href="'. $_SERVER['REQUEST_URI'].'&query=da"><button class="btn btn-info">Query</button></a>
											</a>
										</div>
									</div>
								</div>
								<br><br>
								';
						}
							
						//Afisam numele fiecarei coloane
						$sql3 = mysqli_query($con, "SELECT COLUMN_NAME FROM information_schema.columns WHERE table_schema='". $_GET['nume_db'] ."' AND table_name = '". $_GET['nume_tabel'] ."'");
						echo '<thead>
								<tr>';
									while($rand3 = mysqli_fetch_assoc($sql3))
										echo '<th>'. $rand3['COLUMN_NAME'] .'</th>';
							echo '</tr>
							</thead>';
							
							
						// selectam articolele din baza de date
						$query=mysqli_query($con,"SELECT COUNT(*) AS total FROM `". $_GET['nume_tabel'] ."`" );
						$nr=mysqli_fetch_assoc($query);
							  
						$nr_total_de_articole=$nr['total'];  // numarul total de articole care le-am gasit în baza de date
						$nr_articole_pe_pagina=25;  // aici definim numarul de articole afisate pe o pagina
						$nr_de_pagini=ceil($nr_total_de_articole/$nr_articole_pe_pagina);  // calculam câte pagini vor fi în total
							  
						// daca variabila p este setata si este de tip numeric atunci pagina curenta va lua valoarea variabilei p, altfel va lua valoarea 1
						if(isset($_GET['p']) && is_numeric($_GET['p']))
							$pagina_curenta=(int)$_GET['p'];
						else
							$pagina_curenta=1;
							 

						// daca pagina curenta are valoarea mai mare decât numarul de pagini atunci o setam la valoarea ultimei pagini
						if($pagina_curenta > $nr_de_pagini)
							$pagina_curenta=$nr_de_pagini;
							 
						// daca pagina curenta este mai mica decât 1 atunci o resetam la valoarea 1
						elseif($pagina_curenta < 1)
							$pagina_curenta=1;
							

						// definim variabila paginare prin afisarea paginii curente si a numarului total de pagini existente
						$paginare='<a class="paginare">Pagina '.$pagina_curenta.' din '.$nr_de_pagini.'</a><br>';

						$interval=3;  // setam numarul de pagini din jurul paginii curente pentru care afisam link-uri

						// daca pagina curenta este mai mare decât intervalul setat atunci afisam link-uri pentru prima pagina si pentru pagina înapoi
						if($pagina_curenta > (1+$interval)){
							$pagina_inapoi=$pagina_curenta-1;
							$paginare.='<a class="paginare" href="'. $_SERVER['REQUEST_URI'].'&p='.$pagina_inapoi.'">«</a>';
						}
						// daca pagina curenta este mai mare decât 1 si mai mica sau egala decât intervalul setat atunci afisam link doar pentru pagina înapoi
						elseif(($pagina_curenta > 1) && ($pagina_curenta <= (1+$interval))){
							$pagina_inapoi=$pagina_curenta-1;
							$paginare.='<a class="paginare" href="'. $_SERVER['REQUEST_URI'].'&p='.$pagina_inapoi.'">«</a> ';
						}

						// calculam link-urile pentru paginile care trebuie afisate
						for($x=($pagina_curenta - $interval); $x < (($pagina_curenta + $interval) + 1); $x++){
							if(($x > 0) && ($x <= $nr_de_pagini))  
								if($pagina_curenta != $x)
									$paginare.=' <a class="paginare" href="'. $_SERVER['REQUEST_URI'].'&p='.$x.'">'.$x.'</a>' ;
								else
									$paginare.=' <a class="paginare" style="background-color:#ffffff; color:#000000;">'.$x.'</a> ';  // link-ul pentru pagina curenta îl afisam cu alta culoare
						}

						// daca pagina curenta nu este ultima si este mai mica decât intervalul setat atunci afisam link-uri pentru ultima pagina si pentru pagina înainte
						if(($pagina_curenta != $nr_de_pagini) && ($pagina_curenta < ($nr_de_pagini - $interval))){
							$pagina_inainte=$pagina_curenta+1;
							$paginare.='<a class="paginare" href="'. $_SERVER['REQUEST_URI'].'&p='.$pagina_inainte.'">»</a>';
						}
						// daca pagina curenta nu este ultima si este mai mare sau egala decât intervalul setat atunci afisam doar link-ul pentru pagina înainte
						elseif(($pagina_curenta != $nr_de_pagini) && ($pagina_curenta >= ($nr_de_pagini - $interval))){
							$pagina_inainte=$pagina_curenta+1;
							$paginare.=' <a class="paginare" href="'. $_SERVER['REQUEST_URI'].'&p='.$pagina_inainte.'">»</a>';
						}

						$inceput=($pagina_curenta - 1) * $nr_articole_pe_pagina;  // stabilim intrarea din baza de date de unde începe select-ul pentru pagina curenta
							
						//Daca s-a gasit cel putin un rand, afisam datele
						if($afisare){
							$query_afisare_articole= mysqli_query($con,"SELECT * FROM `". $_GET['nume_tabel'] ."` LIMIT $inceput, $nr_articole_pe_pagina");
							
							//Afisare informatii din baza de date
							while($rand = mysqli_fetch_assoc($query_afisare_articole)){
								echo '<tr>';
								foreach($rand as $field){
									echo '<td>' . htmlspecialchars($field) . '</td>';
								}
								
								echo '</tr>';
							} 
						} else
								echo '<tr><td><i>Nu s-au gasit informatii<i></td></center></tr>';
							echo '
							</tbody>
						</table><br />';
						print $paginare;  // afisam link-urile paginarii creeate mai sus
					} else { // Daca s-a definit 'nume_tabel'
					
						//Daca s-a definit 'query'
						if(isset($_GET['query']) && $_GET['query'] == "da" && !isset($_GET['editare'])){
							if(isset($_POST['submit'])){
								$sql = mysqli_query($con, "". $_POST['query'] ."");
									
								if(!$sql)
									die('Query-ul nu este structurat corect!');
								else
									die('Randul a fos inserat cu succes!');
						}					
						?>
						<br>
							<form method="POST" action="">
								<textarea class="form-control" name="query" rows="5" placeholder='Introduceti codul sql fara ""'></textarea>
								<br />
								<button class="btn btn-info" type="submit" name="submit">Insereaza</button>
							</form>
						<?php
						}
							if(isset($_GET['editare']) && !isset($_GET['coloana'])){ //Daca nu s-a definit 'coloana' afisam formularul
								echo '<form method="POST" action="">';
								//Daca s-au trimis datele, redirectionam
								if(isset($_POST['submit']))
									echo '
										<script>
											window.location.replace("'.$_SERVER['REQUEST_URI'].'&coloana='.$_POST['coloana'].'&indice1='.$_POST['indice1'].'&indice2='.$_POST['indice2'].'");
										</script>
									';
							?>
							<div class="form-group col-md-6">
								Nume coloana de editat :
								<input type="text" required class="form-control" name="coloana" placeholder="Nume coloana"/>
							</div>
							
							<div class="form-group col-md-6">
								Indice unic (WHERE X):
								<input type="text" required class="form-control" name="indice1" placeholder="Indice unic X"/>
							</div>
							
							<div class="form-group col-md-6">
								Indice unic (WHERE X = Y):
								<input type="text" required class="form-control" name="indice2" placeholder="Indice unic Y"/>
							</div>
									
							<div class="form-group col-md-6">
								<button type="submit" class="btn btn-info" name="submit">Trimite</button>
							</div>
						</form>
						<?php
							} else if(isset($_GET['editare'])){ //Altfel afisam formularul de editare al coloanei
							
					
								echo '<form method="POST" action="">';
								
								$sql = mysqli_query($con, "SELECT `". $_GET['coloana'] ."` FROM `". $_GET['nume_tabel'] ."` WHERE `". $_GET['indice1'] ."` = '". $_GET['indice2'] ."'");
								
								//Daca nu s-a gasit niciun rand sau query-ul este gresit afisam eroare
								if(mysqli_num_rows($sql) == 0 || !$sql)
									die('Nu s-a gasit nicio informatie');
								
								//Daca s-au trimis date, redirectionam
								if(isset($_POST['submit'])){
									mysqli_query($con, "UPDATE `". $_GET['nume_tabel'] ."` SET `". $_GET['coloana'] ."` = '". $_POST['editare'] ."' WHERE `". $_GET['indice1'] ."` = '". $_GET['indice2'] ."'");
									
									echo '
										<script>
											window.location.replace("?actiune=db&host='.$_GET['host'].'&nume_db='.$_GET['nume_db'].'&utilizator='.$_GET['utilizator'].'&parola='.$_GET['parola'].'&nume_tabel='.$_GET['nume_tabel'].'");
										</script>
									';									
								}
								
								//Afisam valoare coloanei respective
								$val_init = mysqli_fetch_assoc(mysqli_query($con, "SELECT `". $_GET['coloana'] ."` FROM `". $_GET['nume_tabel'] ."` WHERE `". $_GET['indice1'] ."` = '". $_GET['indice2'] ."'"));
								?>
									<div class="form-group col-md-6">
										Editare:
										<input type="text" required class="form-control" name="editare" value="<?php echo $val_init[$_GET['coloana']]; ?>"/>
									</div>
											
									<div class="form-group col-md-6">
										<button type="submit" class="btn btn-info" name="submit">Trimite</button>
									</div>
								</form>
							<?php
							}
							
						} //Daca nu s-a definit 'editare'
					} // Daca s-a definit "nume_tabel"
				} // Conectare baza de date
			} // Completare date baza de date
			

		} else {
			echo 'Nu se afiseaza nimic';
		}
		?>
	</body>
</html>