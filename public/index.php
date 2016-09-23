<?php require_once('Connections/conexionrs.php'); ?>
<?php
if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}

$currentPage = $_SERVER["PHP_SELF"];

function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
  $theValue = (!get_magic_quotes_gpc()) ? addslashes($theValue) : $theValue;

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? "'" . doubleval($theValue) . "'" : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form1")) {
  $insertSQL = sprintf("INSERT INTO publicaciones (usuario, titulo, texto, fecha, amigo, carpeta) VALUES (%s, %s, %s, %s, %s, %s)",
                       GetSQLValueString($_POST['usuario'], "text"),
                       GetSQLValueString($_POST['titulo'], "text"),
                       GetSQLValueString($_POST['texto'], "text"),
                       GetSQLValueString($_POST['fecha'], "date"),
                       GetSQLValueString($_POST['amigo'], "text"),
					   GetSQLValueString($_POST['rut'], "text"));

  mysql_select_db($database_conexionrs, $conexionrs);
  $Result1 = mysql_query($insertSQL, $conexionrs) or die(mysql_error());

  $insertGoTo = "index2.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
}
?>
<?php

$misesion_usercorreo1 = "0";
if (isset($_SESSION['MM_UsuarioRedSocial'])) {
  $misesion_usercorreo1 = $_SESSION['MM_UsuarioRedSocial'];
}
mysql_select_db($database_conexionrs, $conexionrs);
$query_usercorreo1 = sprintf("SELECT tblusuario.strEmail FROM tblusuario WHERE tblusuario.idContador = %s", GetSQLValueString($misesion_usercorreo1, "int"));
$usercorreo1 = mysql_query($query_usercorreo1, $conexionrs) or die(mysql_error());
$row_usercorreo1 = mysql_fetch_assoc($usercorreo1);
$totalRows_usercorreo1 = mysql_num_rows($usercorreo1);

$maxRows_todaslaspub = 10;
$pageNum_todaslaspub = 0;
if (isset($_GET['pageNum_todaslaspub'])) {
  $pageNum_todaslaspub = $_GET['pageNum_todaslaspub'];
}
$startRow_todaslaspub = $pageNum_todaslaspub * $maxRows_todaslaspub;

$userkc_logeo = "0";
if (isset($row_usercorreo1['strEmail'])) {
  $userkc_logeo = $row_usercorreo1['strEmail'];
}

$la_carpeta = "0";
if (isset($_GET['cpt'])) {
  $la_carpeta = $_GET['cpt'];
}

mysql_select_db($database_conexionrs, $conexionrs);

$query_todaslaspub = sprintf("Select
  publicaciones.id,
  publicaciones.usuario,
  publicaciones.titulo,
  publicaciones.texto,
  publicaciones.fecha,
  publicaciones.amigo,
  publicaciones.imagen,
  publicaciones.carpeta,
  (Select
    Count(comentarios.id) As cntCom
  From
    comentarios
  Where
    publicaciones.id = comentarios.idpublicacion) As numcom,
  (Select
    Count(megusta.id) As cntMeg
  From
    megusta
  Where
    publicaciones.id = megusta.idpublicacion) As numgusta,
  evento.descripcion,
  tblusuario.strNombre,
  tblusuario.idContador
From
  publicaciones,
  comentarios,
  tblusuario,
  invitados invitados1,
  invitados,
  evento
Where
  invitados1.carpeta = evento.carpeta And
  publicaciones.carpeta = '".mysql_real_escape_string($la_carpeta)."' And
  invitados1.carpeta = '".mysql_real_escape_string($la_carpeta)."' And
  invitados.usuario = '".mysql_real_escape_string($userkc_logeo)."' And
  publicaciones.amigo = tblusuario.strEmail
Group By
  publicaciones.id, publicaciones.usuario, publicaciones.titulo,
  publicaciones.texto, publicaciones.fecha, publicaciones.amigo,
  publicaciones.imagen, publicaciones.carpeta, (Select
    Count(comentarios.id) As cntCom
  From
    comentarios
  Where
    publicaciones.id = comentarios.idpublicacion), (Select
    Count(megusta.id) As cntMeg
  From
    megusta
  Where
    publicaciones.id = megusta.idpublicacion), evento.descripcion,
  tblusuario.strNombre, publicaciones.amigo, tblusuario.idContador
Order By
  publicaciones.fecha Desc", 
  GetSQLValueString($la_carpeta, "text"), 
  GetSQLValueString($userkc_logeo, "text"));

//$query_limit_todaslaspub = sprintf("%s LIMIT %d, %d", $query_todaslaspub, $startRow_todaslaspub, $maxRows_todaslaspub);
$todaslaspub = mysql_query($query_todaslaspub, $conexionrs) or die(mysql_error());
$row_todaslaspub = mysql_fetch_assoc($todaslaspub);

if (isset($_GET['totalRows_todaslaspub'])) {
  $totalRows_todaslaspub = $_GET['totalRows_todaslaspub'];
} else {
  $all_todaslaspub = mysql_query($query_todaslaspub);
  $totalRows_todaslaspub = mysql_num_rows($all_todaslaspub);
}
$totalPages_todaslaspub = ceil($totalRows_todaslaspub/$maxRows_todaslaspub)-1;

$queryString_todaslaspub = "";
if (!empty($_SERVER['QUERY_STRING'])) {
  $params = explode("&", $_SERVER['QUERY_STRING']);
  $newParams = array();
  foreach ($params as $param) {
    if (stristr($param, "pageNum_todaslaspub") == false && 
        stristr($param, "totalRows_todaslaspub") == false) {
      array_push($newParams, $param);
    }
  }
  if (count($newParams) != 0) {
    $queryString_todaslaspub = "&" . htmlentities(implode("&", $newParams));
  }
}
$queryString_todaslaspub = sprintf("&totalRows_todaslaspub=%d%s", $totalRows_todaslaspub, $queryString_todaslaspub);

//inserta el megusta
$editFormAction3 = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction3 .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if ((isset($_POST["MM_insert3"])) && ($_POST["MM_insert3"] == "form3")) {
  $insertSQL3 = sprintf("INSERT INTO megusta (usuario, gusta, fecha, idpublicacion) VALUES (%s, %s, %s, %s)",
                       GetSQLValueString($_POST['usuario'], "text"),
                       GetSQLValueString($_POST['megusta'], "text"),
                       GetSQLValueString($_POST['fecha'], "date"),
                       GetSQLValueString($_POST['idpublicacion'], "int"));

  mysql_select_db($database_conexionrs, $conexionrs);
  $Result3 = mysql_query($insertSQL3, $conexionrs) or die(mysql_error());

  $insertGoTo = "index2.php";
  if (isset($_SERVER['QUERY_STRING'])) {
    $insertGoTo .= (strpos($insertGoTo, '?')) ? "&" : "?";
    $insertGoTo .= $_SERVER['QUERY_STRING'];
  }
  header(sprintf("Location: %s", $insertGoTo));
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/principal.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Drone</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
<link href="css/estilos.css" rel="stylesheet" type="text/css" />

	<!-- Stylesheets -->
	<link type="text/css" rel="stylesheet" href="css/reset.css" />
	<link type='text/css' rel='stylesheet' href='http://fonts.googleapis.com/css?family=Titillium+Web:400,600,700' />
	<link type="text/css" rel="stylesheet" href="css/font-awesome.min.css" />
	<link type="text/css" rel="stylesheet" href="css/ot-menu.css" />
	<link type="text/css" rel="stylesheet" href="css/main-stylesheet.css" />
	<link type="text/css" rel="stylesheet" href="css/shortcodes.css" />
	<link type="text/css" rel="stylesheet" href="css/responsive.css" />

<?php include("includes/google.php"); ?>
</head>

<body>

<div class="container">
  <div class="header"><?php include("includes/cabecera.php"); ?><!-- end .header --></div>
  <div class="sidebar1"><?php include("includes/lateralizquierda.php"); ?><!-- end .sidebar1 --></div>
  <div class="content"><!-- InstanceBeginEditable name="RegionContenido" --><?php if (isset( $_SESSION['MM_UsuarioRedSocial'])){?>
    
<div><p>Publicaciones <?php echo $row_todaslaspub['descripcion']; ?></p></div>    
    
  <?php if ($row_todaslaspub == 0) { // Show if recordset empty ?>
    <p>No hay ninguna publicaci&oacute;n</p>
    <?php } // Show if recordset empty ?>
  <?php if ($row_todaslaspub > 0) { // Show if recordset not empty ?>
  <?php do { ?>

	<div class="cuadroblanco">
	<div id="contenedor">

<?php if (is_file("images/usuarios/".$row_todaslaspub['idContador'].".jpg")){?>
    
<div id="antes"><p><a href="perfilamigo.php?amiguis=<?php echo $row_todaslaspub['amigo']; ?>"><img src="images/usuarios/<?php echo $row_todaslaspub['idContador']; ?>.jpg" width="40" height="40"></a></div> 

<?php }
		  else
		  {?>

<div id="antes"><p><a href="perfilamigo.php?amiguis=<?php echo $row_todaslaspub['amigo']; ?>"><img id="imagenusuario" src="images/usuarioblanco.jpg" width="40" height="40" /></a></div>  

<?php }?>

<div id="izquierda"><a href="perfilamigo.php?amiguis=<?php echo $row_todaslaspub['amigo']; ?>" class="nounderline"><?php echo $row_todaslaspub['strNombre']; ?></a></div><?php 
	if($row_todaslaspub['usuario']==$row_todaslaspub['amigo']){
		echo " ";
		} 
		else{
			?><div id="izquierda"><?php echo " > "; ?>
            <a href="perfilamigo.php?amiguis=<?php echo $row_todaslaspub['usuario']; ?>" class="nounderline"><?php echo $row_todaslaspub['usuario']; ?></a></div>
			<?php 
			}?>
           </p>
        </div>
        <div id="fecha"><p><?php echo $row_todaslaspub['fecha']; ?></p></div>
        <div class="letranormal2"><p><?php echo $row_todaslaspub['titulo']; ?></p>
          
          <?php if ($row_todaslaspub['imagen']=='no'){}
		      else {
				  ?>
                  <img class="redondeado" src="<?php echo $row_todaslaspub['imagen']; ?>" width="400" height="400" />
				  <?php 
				  } 
        ?>
        <p></p>
        </div>

<table width="400px" border="0" cellspacing="3" cellpadding="0">
<tr>
<td><p><?php echo $row_todaslaspub['texto']; ?></p></td>
</tr>
</table>

<br />

<div class="cuadroblanco">       
<table border="0" style="width:210px padding: 7px;">
<tr>
  <td><form action="<?php echo $editFormAction3; ?>" method="post" name="form3" id="form3">
      <input type="hidden" name="usuario" value="<?php echo $row_usercorreo1['strEmail']; ?>" />
      <input type="hidden" name="megusta" value="1" />
      <input type="hidden" name="fecha" value="" />
      <input type="hidden" name="idpublicacion" value="<?php echo $row_todaslaspub['id']; ?>" />
      <span style='color: #01B57F;'><label><input type="submit" value="form3" class="invisibutton">Me gusta</label>
      <input type="hidden" name="MM_insert3" value="form3" />
   </form></td>
  <td><a href="comentar.php?idpub=<?php echo $row_todaslaspub['id']; ?>">Comentar</a></td>
  <td><img src="images/comentario.png" width="20" height="19" alt="comentarios" /> <?php echo $row_todaslaspub['numcom']; ?></td>
  <td><img src="images/dia1.png" width="20" height="19" alt="comentarios" /> <?php echo $row_todaslaspub['numgusta']; ?></td>
</tr>
</table>
</div>   
         
      </div>
      <br /> 	
        
		<?php } while ($row_todaslaspub = mysql_fetch_assoc($todaslaspub)); ?>
		<?php } // Show if recordset not empty ?>
		
        <table border="0">
          <tr>
            <td><?php if ($pageNum_todaslaspub > 0) { // Show if not first page ?>
                <a href="<?php printf("%s?pageNum_todaslaspub=%d%s", $currentPage, 0, $queryString_todaslaspub); ?>"><img src="First.gif" /></a>
                <?php } // Show if not first page ?></td>
            <td><?php if ($pageNum_todaslaspub > 0) { // Show if not first page ?>
                <a href="<?php printf("%s?pageNum_todaslaspub=%d%s", $currentPage, max(0, $pageNum_todaslaspub - 1), $queryString_todaslaspub); ?>"><img src="Previous.gif" /></a>
                <?php } // Show if not first page ?></td>
            <td><?php if ($pageNum_todaslaspub < $totalPages_todaslaspub) { // Show if not last page ?>
                <a href="<?php printf("%s?pageNum_todaslaspub=%d%s", $currentPage, min($totalPages_todaslaspub, $pageNum_todaslaspub + 1), $queryString_todaslaspub); ?>"><img src="Next.gif" /></a>
                <?php } // Show if not last page ?></td>
            <td><?php if ($pageNum_todaslaspub < $totalPages_todaslaspub) { // Show if not last page ?>
                <a href="<?php printf("%s?pageNum_todaslaspub=%d%s", $currentPage, $totalPages_todaslaspub, $queryString_todaslaspub); ?>"><img src="Last.gif" /></a>
                <?php } // Show if not last page ?></td>
          </tr>
        </table>
<?php }?><!-- InstanceEndEditable -->
  <!-- end .content --></div>
  <div class="footer"><?php include("includes/pie.php"); ?><!-- end .footer --></div>
  <!-- end .container --></div>
</body>
<!-- InstanceEnd --></html>
<?php
mysql_free_result($todaslaspub);

mysql_free_result($usercorreo1);
?>
