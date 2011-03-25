<?php
//	Sistema de Relatórios USP para Moodle
//	Copyright (C) 2010 Neomundi Internet
//
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

// Página responsável pela apresentação do Diário do Tutor

require_once('../../config.php' );
//require_once('lib.php');

// Caceçalho
$courseid = required_param('id', PARAM_INT);
$course = get_record('course','id', $courseid);
$navigation = array(
              array('name' => $course->shortname, 'link' => "{$CFG->wwwroot}/course/view.php?id=$course->id", 'type'=> 'title'),
              array('name' => get_string('tutordiary', 'block_term'), 'link'=>'', 'type'=>'title'),
                );
print_header_simple(get_string('headerterm', 'block_term'),'', build_navigation($navigation));

?>

<?php
// Obtem o Id do curso e o Id de instância do bloco
$id = required_param('id', PARAM_INT);
$instanceid = required_param('instanceid', PARAM_INT);
$titleterm = required_param('titleterm');
$institution = required_param('institution');
$courses = get_my_courses($USER->id, 'category DESC, fullname ASC');
$courses = prepCourseCategories($courses);//informacoes de cursos e categorias

//FUNCTIONS *********************************************************

//Lista com Cursos e Categorias
function prepCourseCategories($arCourses) {
	$cats = get_records('course_categories');
	$arCats= array();
	foreach ($cats as $cat) {
		$arCats[$cat->id] = array($cat->name, $cat->depth, $cat->sortorder);
	}
	foreach ($arCourses as $course) {
		$arListing['id'] = $course->id;
		$arListing['visible'] = $course->visible;
		$arListing['shortname'] = $course->shortname;
		$arListing['fullname'] = $course->fullname;
		$arListing['category'] = $course->category;
		$arListing['categoryname'] = $arCats[$course->category][0];
		$arListing['categorydepth'] = $arCats[$course->category][1];
		$arListing['categorysortorder'] = $arCats[$course->category][2];
		
		$arListings[] = $arListing;
	}

	usort($arListing, array(&$this, "listingCmp"));

	return $arListings;
}

?>
<h1 id="titleterm"><?php print $titleterm; ?></h1>

<p>
Eu, <b><?php print $USER->firstname .' '. $USER->lastname; ?></b>, portador(a) do RG ou RNE n°<input type="text" name="rg" id="rg"> e do CPF n°<input type="text" name="cpf" id="cpf">, tutor do Curso <b><?php print $courses[0]['categoryname']; ?> <?php print $institution; ?></b>, declaro para os devidos fins concordar com a utilização, para fins acadêmicos, das informações contidas no ambiente virtual de aprendizagem. Tenho ciência que os responsáveis pela condução da pesquisa asseguram o anonimato dos alunos e dos tutores por meio da supressão do nome e/ou qualquer sinal identificador dos participantes. Declaro compreender que as informações obtidas só podem ser usadas para fins científicos, de acordo com a ética da academia e que a participação nessa pesquisa não comporta qualquer remuneração. 

</p>
<p>
<button id="generate"><?php print_string('showdiary', 'block_term');?></button>
</p>



<?php
print_footer(); 
?>
