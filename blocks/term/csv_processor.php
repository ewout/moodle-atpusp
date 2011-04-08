<?php
//	This program is free software: you can redistribute it and/or modify
//	it under the terms of the GNU General Public License as published by
//	the Free Software Foundation, either version 3 of the License, or
//	(at your option) any later version.
//
//	This program is distributed in the hope that it will be useful,
//	but WITHOUT ANY WARRANTY; without even the implied warranty of
//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//	GNU General Public License for more details.
//
//	You should have received a copy of the GNU General Public License
//	along with this program.  If not, see <http://www.gnu.org/licenses/>.

// Esta pagina recebe um conteúdo CSV na variável CSV oriunda de um POST e 
// coloca os cabeçalhos para que seja possível baixar este conteúdo como
// um arquivo CSV com o nome especificado.

$out = $_POST['csv'];
$name = $_POST['name'];
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Length: " . strlen($out));
header("Content-type: application/csv; charset=iso-8859-1");
header("Content-Disposition: attachment; filename=\"".$name."\"");
// Converte UTF-8 para ISO-8859-1 que é o padrão esperado para arquivos CSV.
echo utf8_decode(stripcslashes($out));
?>
