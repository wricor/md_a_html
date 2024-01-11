<?php
require 'var.php';
$fArchivo = file_get_contents('content.txt');
$aContenido = explode("\r\n", $fArchivo);
$origin = array('á', 'é', 'í', 'ó', 'ú', ' ');
$replace = array('a', 'e', 'i', 'o', 'u', '_');
$aStyle = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><style>.code {font-family: "SFMono-Regular", Menlo, Consolas, "PT Mono", "Liberation Mono", Courier, monospace;background: rgba(135, 131, 120, 0.15);padding: 0.1em 1em 1em 1em;}.code-wrap {white-space: pre-wrap;word-break: break-all;}</style></head><body>';
foreach ($aContenido as $iLlave => $sValor) {
	$aValor = explode(", ", $sValor);
	if (isset($aValor[2]) == 1) {
		// Crear el archivo en el que se va a escribir
		$nomArchivo = str_replace($origin, $replace, $aValor[1]);
		$nomArchivo = strtolower($nomArchivo);
		$file = fopen(__DIR__ . '\\html\\' . $nomArchivo . '.php', 'w');
		fwrite($file, $aStyle);
		// Trae el contenido en texto
		$sContenido = file_get_contents($aValor[2]);
		$aContenidoT = explode("\r\n", $sContenido);
		$bEsCodigo = false;
		$bEsLista = false;
		$bEsTabla = false;
		$iCodigo = 0;
		$iListaUl = 0;
		$iListaOl = 0;
		$iTabla = 0;
		foreach ($aContenidoT as $iLlaveT => $sValorT) {
			$bEsParrafo = true;
			// Cambiar los caracteres especiales a HTML
			$sValorT = str_replace('<?', '&lt;&quest;', $sValorT);
			$sValorT = str_replace('\>', '&gt;', $sValorT);
			$sValorT = str_replace('\<', '&lt;', $sValorT);
			$sValorT = str_replace('>', '&gt;', $sValorT);
			$sValorT = str_replace('<', '&lt;', $sValorT);
			// Bloques de código fuente de un línea
			$regEx = '/```(.*?)```/';
			if (preg_match($regEx, $sValorT) == TRUE) {
				$sValorT = preg_replace($regEx, "<code>$1</code>", $sValorT);
			}
			// Bloques de código fuente de varias líneas
			$regEx = '/```.*/';
			if (preg_match($regEx, $sValorT) == TRUE) {
				if (!$bEsCodigo) {
					$sValorT = preg_replace($regEx, '<pre class="code code-wrap"><code>', $sValorT);
					$bEsCodigo = true;			
				} else {
					$sValorT = str_replace('```', '</code></pre>', $sValorT);
					$bEsCodigo = false;
					$bEsParrafo = false;
				}
				$bEsLista = false;
				$bEsTabla = false;
			}
			if (!$bEsCodigo) {
				// Línea vacía
				if ($sValorT == '') {
					$sValorT = str_replace('', '', $sValorT);
					$bEsParrafo = false;
					$bEsLista = false;
					$bEsTabla = false;
				}
				// Bloques de título
				if (stristr($sValorT, '#### ') == TRUE) {
					$sValorT = str_replace('#### ', '<h4>', $sValorT);
					$sValorT = $sValorT . '</h4>';
					$bEsParrafo = false;
					$bEsLista = false;
					$bEsTabla = false;
				}
				if (stristr($sValorT, '### ') == TRUE) {
					$sValorT = str_replace('### ', '<h3>', $sValorT);
					$sValorT = $sValorT . '</h3>';
					$bEsParrafo = false;
					$bEsLista = false;
					$bEsTabla = false;
				}
				if (stristr($sValorT, '## ') == TRUE) {
					$sValorT = str_replace('## ', '<h2>', $sValorT);
					$sValorT = $sValorT . '</h2>';
					$bEsParrafo = false;
					$bEsLista = false;
					$bEsTabla = false;
				}
				if (stristr($sValorT, '# ') == TRUE) {
					$sValorT = str_replace('# ', '<h1>', $sValorT);
					$sValorT = $sValorT . '</h1>';
					$bEsParrafo = false;
					$bEsLista = false;
					$bEsTabla = false;
				}
				// Tablas
				if (substr($sValorT, 0, 1) == '|') {
					$sTabla = '';
					$sValorT = substr($sValorT, 1, -1);
					if (!$bEsTabla) {
						$sTabla = '<table>';
						$sValorT = $sTabla . '<tr><th>' . $sValorT;
						$sValorT = str_replace(' ', '', $sValorT);
						$sValorT = str_replace('|', '</th><th>', $sValorT);
						$sValorT = $sValorT . '</th></tr>';
					} else {
						if (substr($sValorT, 0, 1) == '-') {
							$sValorT = str_replace('|', '', $sValorT);
							$sValorT = str_replace('-', '', $sValorT);
							$sValorT = str_replace(':', '', $sValorT);
							$sValorT = str_replace("\n", '', $sValorT);
						} else {
							$sValorT = $sTabla . '<tr><td>' . $sValorT;
							$sValorT = str_replace(' ', '', $sValorT);
							$sValorT = str_replace('|', '</td><td>', $sValorT);
							$sValorT = $sValorT . '</td></tr>';
							$iTabla++;
						}
					}
					$bEsParrafo = false;
					$bEsLista = false;
					$bEsTabla = true;
				}
				// Negrilla
				if (stristr($sValorT, '**') == TRUE) {
					$regEx = '/\*\*(.*?)\*\*/';
					$sValorT = preg_replace($regEx, "<b>$1</b>", $sValorT);
				}
				// Imagenes (estan con ![ ]())
				$regEx = '/!\[(.*?)\]\(\.\.\/\.\.\/(.*?)\)/';
				if (preg_match($regEx, $sValorT) == TRUE) {
					$sValorT = preg_replace($regEx, "<img src='../" . $var->raizArchivos . "$2' alt='$1' />", $sValorT);
					$bEsParrafo = false;
					$bEsLista = false;
					$bEsTabla = false;
				}
				// Enlaces (estan con [ ]())
				$regEx = '/\[(.*?)\]\((.*?)\)/';
				if (preg_match($regEx, $sValorT) == TRUE) {
					$sValorT = preg_replace($regEx, "<a href='$2'>$1</a>", $sValorT);
					$bEsParrafo = false;
					$bEsLista = false;
					$bEsTabla = false;
				}
				// Listas ordenadas (por el momento deben estar con un solo numero y un punto)
				// @@@@ Si son listas pero tienen otras condiciones?, por ejemplo código
				if (is_numeric(substr($sValorT, 0, 1))) {
					$sOl = '';
					if (!$bEsLista) {
						$sOl = '<ol>';
					}
					$sValorT = $sOl . str_replace(substr($sValorT, 0, 2), '<li>', $sValorT);
					$sValorT = $sValorT . '</li>';
					$bEsParrafo = false;
					$bEsLista = true;
					$bEsTabla = false;
					$iListaOl++;
				}
				// Listas no ordenadas (deben estar con guión - no con asterisco *)
				if (substr($sValorT, 0, 2) == '- ') {
					$sUl = '';
					if (!$bEsLista) {
						$sUl = '<ul>';
					}
					$sValorT = $sUl . str_replace('- ', '<li>', $sValorT);
					$sValorT = $sValorT . '</li>';
					$bEsParrafo = false;
					$bEsLista = true;
					$bEsTabla = false;
					$iListaUl++;
				}
				// Bloques de texto (parrafos)
				if ($bEsParrafo) {
					// Referencia bibliografica _ _
					$sValorT = str_replace('(_', '(', $sValorT);
					$sValorT = str_replace('_,', ',', $sValorT);
					$sValorT = str_replace('\*', '*', $sValorT);
					$sValorT = '<p>' . $sValorT . '</p>';
					$bEsLista = false;
					$bEsTabla = false;
				}
				if (!$bEsLista) {
					if ($iListaOl != 0) {
						$sValorT = '</ol>' . $sValorT;
						$iListaOl = 0;
					}
					if ($iListaUl != 0) {
						$sValorT = '</ul>' . $sValorT;
						$iListaUl = 0;
					}
				}
				if (!$bEsTabla && $iTabla != 0) {
					$sValorT = '</table>' . $sValorT;
					$iTabla = 0;
				}
			}
			if ($sValorT != '') {
				if (fwrite($file, $sValorT)) {
					if ($bEsCodigo) {
						fwrite($file, "\r\n");
					}
				}
			}
		}
		if (fwrite($file, '</body></html>')) {
			echo 'Archivo terminado: ' . $aValor[1] . '<br>';
		}
		if (fclose($file)) {
			echo 'Archivo finalizado: ' . $aValor[1] . '<br>';
		}
	}
}
