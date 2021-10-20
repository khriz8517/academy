<?php

// use core_completion\progress;
// use core_course\external\course_summary_exporter;

error_reporting(E_ALL);
require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->dirroot . '/enrol/externallib.php');
require_once(dirname(__FILE__) . '/../../../course/lib.php');
require_once(dirname(__FILE__) . '/../../../course/modlib.php');

try {
	global $USER, $PAGE;
	$details = $_POST;
	$returnArr = array();

	if (!isset($_REQUEST['request_type']) || strlen($_REQUEST['request_type']) == false) {
		throw new Exception();
	}

	switch ($_REQUEST['request_type']) {
		case 'getModulos':
			$returnArr = getModulos();
			break;
		case 'getCoursesByUser':
			$returnArr = getCoursesByUser();
			break;
		case 'getAllCourses':
			$returnArr = getAllCourses();
			break;
		case 'getActividades':
			$returnArr = getActividades();
			break;
		case 'crearCurso':
			$data = $_REQUEST['data'];
			// $sesskey = $_REQUEST['sesskey'];
			$returnArr = crearCurso($data);
			break;
	}

} catch (Exception $e) {
	$returnArr['status'] = false;
	$returnArr['data'] = $e->getMessage();
}

header('Content-type: application/json');

echo json_encode($returnArr);
exit();

/**
 * * getProgressModule
 * ! MODULO > CURSO > ACTIVIDADES
 * * obtengo el total de actividades relacionadas a un modulo
 * @param $moduleid es el id del modulo
 */

function getProgressModule($moduleid){
	global $DB, $USER;
	$userid = $USER->id;
	$progress = 0;
	$total_modulos = $DB->get_records_sql('select cmod.id from mdl_course_categories cat
					join (select c.id, c.fullname, c.shortname, c.category
								from mdl_user_enrolments as uen
								join mdl_enrol as enrol on uen.enrolid = enrol.id 
								join mdl_course as c on c.id = enrol.courseid where uen.userid = '.$userid.' and c.visible = 1) c on cat.id = c.category
					join mdl_course_modules cmod on cmod.course = c.id
					where cat.id = '.$moduleid.'
					and deletioninprogress = 0
					and module != 9', []);
	$modulos_completados = $DB->get_records_sql('select * from mdl_course_modules_completion
					where completionstate = 1
					and coursemoduleid in (select cmod.id from mdl_course_categories cat
					join (select c.id, c.fullname, c.shortname, c.category
								from mdl_user_enrolments as uen
								join mdl_enrol as enrol on uen.enrolid = enrol.id 
								join mdl_course as c on c.id = enrol.courseid where uen.userid = '.$userid.' and c.visible = 1) c on cat.id = c.category
					join mdl_course_modules cmod on cmod.course = c.id
					where cat.id = '.$moduleid.'
					and deletioninprogress = 0
					and module != 9)', []);
	if(count($total_modulos) > 0 && count($modulos_completados) > 0){
		$progress = count($modulos_completados) * 100 / count($total_modulos); 
	}
	return (object) [
		'progress' => $progress,
		'total_modulos' => count($total_modulos),
		'modulos_completados' => count($modulos_completados),
		'modulos_noiniciados' => count($total_modulos) - count($modulos_completados)
	];
}

function getProgressByCourse($courseid){
	global $DB, $USER;
	$userid = $USER->id;
	$total_modulos = $DB->get_records_sql('select cmod.id from mdl_course_categories cat
					join (select c.id, c.fullname, c.shortname, c.category
								from mdl_user_enrolments as uen
								join mdl_enrol as enrol on uen.enrolid = enrol.id 
								join mdl_course as c on c.id = enrol.courseid where uen.userid = '.$userid.' and c.visible = 1) c on cat.id = c.category
					join mdl_course_modules cmod on cmod.course = c.id
					where c.id = '.$courseid.'
					and deletioninprogress = 0
					and module != 9', []);
	$modulos_completados = $DB->get_records_sql('select * from mdl_course_modules_completion
					where completionstate = 1
					and coursemoduleid in (select cmod.id from mdl_course_categories cat
					join (select c.id, c.fullname, c.shortname, c.category
								from mdl_user_enrolments as uen
								join mdl_enrol as enrol on uen.enrolid = enrol.id 
								join mdl_course as c on c.id = enrol.courseid where uen.userid = '.$userid.' and c.visible = 1) c on cat.id = c.category
					join mdl_course_modules cmod on cmod.course = c.id
					where c.id = '.$courseid.'
					and deletioninprogress = 0
					and module != 9)', []);
	if(count($total_modulos) > 0 && count($modulos_completados) > 0){
		return count($modulos_completados) * 100 / count($total_modulos); 
	}
	return 0;
}


/**
 * TODO: agregar al filtro el id del modulo
 */
function getNotaIntentos($course, $userid){
	global $DB, $USER;
	$intentos = 0;
	$nota = 0;
	$notaintentos = $DB->get_records_sql('SELECT count(*) as intentos, max(puntaje_porcentaje) as puntaje FROM mdl_aq_eval_user_puntaje_data
					where userid = '.$userid.'
					and course = '.$course, []);
	foreach ($notaintentos as $key => $value) {
		$intentos = $value->intentos;
		$nota = $value->puntaje;
	}

	return (object) [
		'intentos' => $intentos,
		'nota' => $nota
	];
}

function getCoursesByUserProgress($progress){
	if($progress == 100){
		return 2;
	}else if($progress > 0 && $progress < 100){
		return 1;
	}else if($progress == 0){
		return 0;
	}else{
		return -1;
	}
}

/** 
 * getModulos
 * * obtengo los modulos
 */
function getModulos() {
	global $DB, $USER;
	$data = $DB->get_records('course_categories', [
		'visible' => 1
	]);
	$output = [];

	$cursos_sql = 'select * from mdl_course_categories cat
					join mdl_course c on cat.id = c.category
					join mdl_course_modules cmod on cmod.course = c.id
					where cat.id = 5
					and deletioninprogress = 0
					and module != 9;'; 
	$dat = $DB->get_records_sql($cursos_sql, []);

	foreach ($data as $key => $value) {
		$progress_data = getProgressModule($value->id);
		array_push($output, [
			'id' => $value->id,
			'mod_short' => $value->name,
			'mod_large' => $value->description,
			'progress' => intval($progress_data->progress),
			'total_modulos' => $progress_data->total_modulos,
			'modulos_completados' => $progress_data->modulos_completados,
			'modulos_noiniciados' => $progress_data->modulos_noiniciados
		]);
	}
	return $output;
}

function getCoursesByUser(){
	global $DB, $USER, $CFG;
	$sql = "select c.id, c.fullname, c.shortname, c.category
			from mdl_user_enrolments as uen
			join mdl_enrol as enrol on uen.enrolid = enrol.id 
			join mdl_course as c on c.id = enrol.courseid where uen.userid = $USER->id and c.visible = 1";
	$data = $DB->get_records_sql($sql, []);
	$output = [];

	foreach ($data as $key => $value) {
		$progress = getProgressByCourse($value->id);
		$nota_intentos = getNotaIntentos($value->id, $USER->id);
		array_push($output, [
			'id' => $value->id,
			'curso' => $value->fullname,
			'shortname' => $value->shortname,
			'mod_id' => $value->category,
			'progress' => intval($progress),
			'link' => $CFG->wwwroot.'/course/view.php?id='.$value->id,
			'curso_img' => null,
			'estado' => getCoursesByUserProgress($progress),
			'nota' => intval($nota_intentos->nota),
			'intentos' => $nota_intentos->intentos
		]);
	}
	return $output;
}

function getAllCourses(){
	global $DB, $USER, $CFG;
	$sql = "select course.id, course.category, course.fullname, course.shortname from mdl_course course
			join mdl_course_categories cat on cat.id = course.category
			where course.id not in (select c.id
				from mdl_user_enrolments as uen
				join mdl_enrol as enrol on uen.enrolid = enrol.id 
				join mdl_course as c on c.id = enrol.courseid
				where uen.userid = $USER->id and c.visible = 1)
			and course.visible = 1";
	$data = $DB->get_records_sql($sql, []);
	$output = [];

	foreach ($data as $key => $value) {
		array_push($output, [
			'id' => $value->id,
			'curso' => $value->fullname,
			'shortname' => $value->shortname,
			'mod_id' => $value->category,
			'progress' => 0,
			'link' => $CFG->wwwroot.'/course/view.php?id='.$value->id,
			'curso_img' => null,
			'estado' => 0,
			'nota' => 0,
			'intentos' => 0
		]);
	}
	return $output;
}

function getActividades(){
	global $DB;
	$actividades = $DB->get_records_list("modules", "name", ["revisionmaterial", "evaluacion", "encuesta", "evidencia", "capacitacion"]);
	return count($actividades);
}

function crearCurso($data){
	global $DB, $USER, $CFG;

	$actividades = $DB->get_records_list("modules", "name", ["revisionmaterial", "evaluacion", "encuesta", "evidencia", "capacitacion"]);

	if(count($actividades) < 5){
		return "Verificar que los modulos revisionmaterial, evaluacion, encuesta, evidencia y capacitacion esten instalados ";
	}

	$dataobj = json_decode($data);
	$course = create_course($dataobj->coursedata);

	$sectionid = $DB->get_record("course_sections", ["course" => $course->id], 'id');

	// //! INSTANCIA MODULOS/ACTIVIDADES ["revisionmaterial", "evaluacion", "encuesta", "evidencia", "capacitacion"]
	$moduleRM = $DB->get_record("modules", ["name" => "revisionmaterial"], 'id');
	$moduleCAP = $DB->get_record("modules", ["name" => "capacitacion"], 'id');
	$moduleEX = $DB->get_record("modules", ["name" => "evaluacion"], 'id');
	$moduleEN = $DB->get_record("modules", ["name" => "encuesta"], 'id');
	$moduleSIS = $DB->get_record("modules", ["name" => "evidencia"], 'id');

	$arraybase = (object)[
		'introeditor' => [
			'text' => "",
			'format' => "1",
			'itemid' => 417418277
		],
		'showdescription' => "0",
		'visible' => 1,
		'visibleoncoursepage' => 1,
		'cmidnumber' => "",
		'groupmode' => "0",
		'groupingid' => "0",
		'availabilityconditionsjson' => `{"op":"&","c":[],"showc":[]}`,
		'completionunlocked' => 1,
		'completion' => "1",
		'completionexpected' => 0,
		'tags' => [],
		'coursemodule' => 0,
		'section' => 0,
		'instance' => 0,
		'update' => 0,
		'return' => 0,
		'sr' => 0,
		'competencies' => [],
		'competency_rule' => "0",
		'submitbutton' => "Save and display",
		'completionattendance' => 0
	];

	$revisionmaterial = addModulo($arraybase, $course, $moduleRM, "1. Revision Material", "revisionmaterial");
	$capacitacion = addModulo($arraybase, $course, $moduleCAP, "2. Capacitación", "capacitacion");
	$evaluacion = addModulo($arraybase, $course, $moduleEX, "3. Examen", "evaluacion");
	$encuesta = addModulo($arraybase, $course, $moduleEN, "4. Encuesta", "encuesta");
	$evidencia = addModulo($arraybase, $course, $moduleSIS, "5. Practicar en el sistema", "evidencia");

	$path_RM = "../materiales/".$course->id;
	if (!file_exists($path_RM)) {
		mkdir($path_RM, 0777, true);
	}

	$numfiles = $_REQUEST['numfiles'];
	for ($i=0; $i < $numfiles; $i++) {
		addMateriales($_FILES["file".$i], $course->id, $moduleRM->id, 0, $dataobj->materiales[$i]->contenido_name);
	}

	//! AÑADIR LAS PREGUNTAS Y SUS OPCIONES
	foreach ($dataobj->preguntas as $key => $value) {
		$enunciado = $value->enunciado;
		$pregunta = $DB->insert_record("aq_evaluacion_data", [
			'course' => $course->id,
			'pregunta' => $enunciado,
			'moduleid' => $evaluacion,
			'module' => $moduleEX->id,
			'created_at' => time(),
		]);
		foreach ($value->opciones as $k => $v) {
			$preg_text = $v->preg_text;
			$checked = $v->checked == true ? 1 : 0;
			if($preg_text !== ""){
				// echo "agregar texto: ".$preg_text;
				$opcion = $DB->insert_record("aq_evaluacion_options_data", [
					'opcion' => $preg_text,
					'is_valid' => $checked,
					'active' => 1,
					'preguntaid' => $pregunta,
					'created_at' => time(),
				]);
			}
		}
	}


	// return $path_RM;
	return [
		'course' => $course,
		'sectionid' => $sectionid,
		'revisionmaterial' => $revisionmaterial
	];

}

function addModulo($arraybase, $course, $module, $name, $modulename){
	$arraybase->name = $name;
	$arraybase->module = $module->id;
	$arraybase->course = $course->id;
	$arraybase->modulename = $modulename;
	$arraybase->add = $modulename;
	return add_moduleinfo($arraybase, $course);
}

function addMateriales($file, $course, $module, $moduleid, $contenido_name){
	global $DB;
	$uploadOk = 0;
	$check = getimagesize($file["tmp_name"]);
	$filename = urlencode(preg_replace("/[^a-zA-Z0-9.]/", "", time().$file['name']));
	$path = "../materiales/".$course."/".$filename;
	$fileType = pathinfo($file['name'], PATHINFO_EXTENSION);
	if($check !== false) {
		$uploadOk = 1;
		if(move_uploaded_file($file['tmp_name'], $path)){
			//agregar base de datos
			$material = $DB->insert_record("aq_material_data", [
				'moduleid' => $moduleid,
				'module' => $module,
				'course' => $course,
				'material_title' => $contenido_name,
				'material_icon' => 'attachment',
				'link_file' => $path,
				'format' => $fileType,
				'active' => 1,
				'created_at' => time(),
			]);
		}else{
			$uploadOk = 0;
		}
	}
	return $uploadOk;
}