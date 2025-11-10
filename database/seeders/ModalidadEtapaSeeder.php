<?php
// database/seeders/ModalidadEtapaSeeder.php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Etapa;
use App\Models\Modalidad;
use App\Models\Nivel;

class ModalidadEtapaSeeder extends Seeder {
  public function run(): void {
    // Resuelve etapas
    $inst = Etapa::firstOrCreate(['nombre'=>'institucional']);
    $circ = Etapa::firstOrCreate(['nombre'=>'circuital']);
    $reg  = Etapa::firstOrCreate(['nombre'=>'regional']);

    $sync = function ($nivelNombre, array $modNombres, array $etapas) {
      $nivel = Nivel::where('nombre',$nivelNombre)->first();
      if (!$nivel) return;
      foreach ($modNombres as $m) {
        $mod = Modalidad::where('nivel_id',$nivel->id)->where('nombre',$m)->first();
        if ($mod) $mod->etapas()->syncWithoutDetaching(collect($etapas)->pluck('id'));
      }
    };

    // Preescolar: solo institucional
    $sync('Preescolar', [
      'Preescolar regular',
      'Preescolar Centros de educación especial',
    ], [$inst]);

    // Primaria I/II (EGB/EPJA): institucional y (si tu regla lo permite) circuital
    $sync('Primaria I Ciclo de la Educación General Básica', [
      'Primaria académica regular','Primaria académica indígena','Primaria académica unidocente',
      'Primaria Educación Especial (Centros de Educación Especial y Aulas Integradas)',
    ], [$inst, $circ]);

    $sync('Primaria I Ciclo de la Educación de Personas Jóvenes y Adultas (EPJA)', [
      'Primaria académica Nocturna','I Nivel del Plan de Estudios de Educación de Adultos (CINDEA e IPEC)',
    ], [$inst, $circ]);

    $sync('Primaria II Ciclo de la Educación General Básica', [
      'Primaria académica regular','Primaria académica indígena','Primaria académica unidocente',
      'Primaria Educación Especial (Centros de Educación Especial y Aulas Integradas)',
    ], [$inst, $circ]);

    $sync('Primaria II Ciclo  de la Educación de Personas Jóvenes y Adultas (EPJA)', [
      'Primaria académica nocturna',
    ], [$inst, $circ]);

    // Secundaria III Ciclo (académica reg/ind): institucional, circuital, regional
    $sync('Secundaria III Ciclo de la Educación General', [
      'Secundaria académica regular','Secundaria académica indígena',
    ], [$inst, $circ, $reg]);

    // Básica y Educación Diversificada
    $sync('Básica y Educación Diversificada', [
      'Secundaria científica y colegios humanísticos',
      'Secundaria técnica',
      'Secundaria Educación Especial (III y IV Ciclos Centros de Educación Especial y Plan Nacional)',
    ], [$inst, $circ, $reg]);

    // Secundaria III Ciclo y Educación Diversificada EPJA
    $sync('Secundaria III Ciclo y Educación Diversificada de la Educación de Personas Jóvenes y Adultas (EPJA)', [
      'Secundaria académica nocturna',
      'II Nivel del Plan de Estudios de Educación de Adultos (CINDEA e IPEC)',
      'III Nivel del Plan de Estudios de Educación de Adultos (CINDEA e IPEC)',
    ], [$inst, $circ, $reg]);
  }
}
