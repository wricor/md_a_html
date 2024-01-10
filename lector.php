<?php
require 'var.php';
// Se obtiene el contenido del menu
$archivo = file_get_contents($var->raizMenu . '_sidebar.md');
// Se divide en arreglo por lineas
$aContenido = explode("\r\n", $archivo);
// Contador para recorrer el arreglo
$iCont = count($aContenido);

$iNivelAnt = 0;
$bMarcaSubNivel = false;
$iNivel1 = 0;
$iNivel2 = 0;
$iNivel3 = 0;
$iSubNivel = 0;
// Crea el archivo del menu
$file = fopen('content.txt', 'w');
for ($i = 0; $i < $iCont; $i++) {
	$iNivel = count(explode('/', $aContenido[$i]));
	if (isset($aContenido[$i + 1]) == 1) {
		$iNivelSig = count(explode('/', $aContenido[$i + 1]));
	}
	if ($iNivel != 1) {
		$iNivel = $iNivel - 2;
	}
	if ($iNivelSig != 1) {
		$iNivelSig = $iNivelSig - 2;
	}
	if ($iNivel == 1 && $iNivelSig > 2) {
		$iNivel = $iNivelSig - 1;
	}

	// Se reemplazan primero los parentesis para que no se dañen los de apertura del arreglo
	$aContenido[$i] = str_replace('(', './docs', $aContenido[$i]);
	$aContenido[$i] = str_replace(')', '', $aContenido[$i]);
	$aContenido[$i] = str_replace("\t", '', $aContenido[$i]);

	if (stristr($aContenido[$i], '* [**') == TRUE) {
		// Reemplaza los niveles 1 con archivo
		$iNivel1 = $iNivel1 + 10000;
		$aContenido[$i] = str_replace('* [**', $iNivel1 . ', ', $aContenido[$i]);
		$aContenido[$i] = str_replace('**]', ', ', $aContenido[$i]);
		$iNivel2 = 0;
		$iNivel3 = 0;
	}

	if (stristr($aContenido[$i], '* **') == TRUE) {
		// Reemplaza los niveles 1 sin archivo
		$iNivel1 = $iNivel1 + 10000;
		$aContenido[$i] = str_replace('* **', $iNivel1 . ', ', $aContenido[$i]);
		$aContenido[$i] = str_replace("**", '', $aContenido[$i]);
		$iNivel2 = 0;
		$iNivel3 = 0;
	}

	if (stristr($aContenido[$i], '* [') == TRUE) {
		// Reemplaza subniveles con archivo
		if ($bMarcaSubNivel) {
			$iNivel3++;
			$iSubNivel = $iNivel1 + $iNivel2 + $iNivel3;
		} else {
			if ($iNivel == 3) {
				$iNivel3++;
				$iSubNivel = $iNivel1 + $iNivel2 + $iNivel3;
			} else {
				$iNivel2 = $iNivel2 + 100;
				$iSubNivel = $iNivel1 + $iNivel2;
				$iNivel3 = 0;
			}
		}
		$aContenido[$i] = str_replace('* [', $iSubNivel . ', ', $aContenido[$i]);
		$aContenido[$i] = str_replace(']', ', ', $aContenido[$i]);
		if ($bMarcaSubNivel) {
			if ($iNivel > 2) {
				$bMarcaSubNivel = false;
			}
		}
	}
	
	// Reemplaza subniveles sin archivo
	if (stristr($aContenido[$i], '* ') == TRUE) {

		if ($iNivel != $iNivelSig) {
			$iNivel2 = $iNivel2 + 100;
			$iSubNivel = $iNivel1 + $iNivel2;
		} else {
			$iNivel3++;
			$iSubNivel = $iNivel1 + $iNivel2 + $iNivel3;
		}
		$aContenido[$i] = str_replace('* ', $iSubNivel . ', ', $aContenido[$i]);
		$bMarcaSubNivel = true;
		$iNivel = $iNivelAnt++;
	}

	$iNivelAnt = $iNivel;
	fwrite($file, $aContenido[$i] . "\r\n");
}
if (fclose($file)) {
	echo 'Proceso terminado';
}