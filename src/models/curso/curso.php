<?php
include_once 'src/bd/condb.php';
include_once 'src/utils/utils.php';

class Curso extends ConDB {

  private $BASEQUERY = '
  SELECT
  pl.id_prd_lectivo,
  prd_lectivo_nombre,
  persona_primer_nombre,
  persona_primer_apellido,
  c.id_curso,
  curso_nombre,
  curso_ciclo,
  curso_capacidad,
  m.id_materia,
  materia_nombre
  FROM
  public."Cursos" c, public."Personas" p,
  public."Docentes" d, public."PeriodoLectivo" pl,
  public."Materias" m
  WHERE
  c.id_prd_lectivo = pl.id_prd_lectivo AND
  d.id_docente = c.id_docente AND
  p.id_persona = d.id_persona AND
  m.id_materia = c.id_materia
  ';
  private $ENDQUERY = '
  AND curso_activo = true
  ORDER BY prd_lectivo_nombre
  ';

  public function __construct() {
    $this->con = new ConDB();
    //$this->ct = $this->con->getCon();
  }

  function cargarCursos() {
    $query =  $this->BASEQUERY .' '. $this->ENDQUERY;

    return $this->sql($query);
  }

  function buscar($id_curso){
    $query = $this->BASEQUERY . "
      AND c.id_curso = $id_curso
    " . $this->ENDQUERY;
    return $this->sql($query);
  }

  function buscarPorAlumno($aguja){
    $query = $this->BASEQUERY . '
      AND c.id_curso IN (
        SELECT id_curso
        FROM public."AlumnoCurso"
        WHERE id_alumno IN (
          SELECT id_alumno
          FROM public."Alumnos" a, public."Personas" p
          WHERE
          a.id_persona = p.id_persona AND (
            '.BDUQuerys::buscarPersona($aguja).'
          )
        )
      )
    ' . $this->ENDQUERY;
    return $this->sql($query);
  }

  function cargarPorDoncente($aguja){
    $query = $this->BASEQUERY . "
      AND (
        '.BDUQuerys::buscarPersona($aguja).'
      )
    " . $this->ENDQUERY;
    return $this->sql($query);
  }

  function cargarCursosPorPeriodo($id_periodo) {
    $query = $this->BASEQUERY . "
      AND c.id_prd_lectivo = $id_periodo
    " . $this->ENDQUERY;
    return $this->sql($query);
  }

  function cargarCursosPorNombrePeriodo($curso_nombre, $id_periodo) {
    $query = $this->BASEQUERY . "
    AND c.curso_nombre ILIKE '%$curso_nombre%'
    AND c.id_prd_lectivo = $id_periodo
    " . $this->ENDQUERY;
    return $this->sql($query);
  }

  function buscarCursos($aguja) {
    $query = $this->BASEQUERY." AND (
      curso_nombre ILIKE '%$aguja%'
      OR ".BDUQuerys::buscarPersona($aguja)."
      OR materia_nombre ILIKE '%$aguja%'
    )".' '. $this->ENDQUERY;
    return $this->sql($query);
  }

  function cargarCursosNombrePorPeriodo($id_periodo){
    $query = '
    SELECT
    DISTINCT curso_nombre
    FROM
    public."Cursos" c
    WHERE
    c.id_prd_lectivo = '.$id_periodo.' AND
    c.curso_activo = true
    ORDER BY curso_nombre;';
    return $this->sql($query);
  }

  function cargarCursosMateriaPorNombrePeriodo($curso_nombre, $id_periodo){
    $query = '
    SELECT
    id_curso,
    materia_nombre
    FROM
    public."Cursos" c,
    public."Materias" m
    WHERE
    m.id_materia = c.id_materia AND
    c.curso_nombre ILIKE \'%'.$curso_nombre.'%\' AND
    c.id_prd_lectivo = '.$id_periodo.'
    ORDER BY materia_nombre;';
    return $this->sql($query);
  }

}

?>
