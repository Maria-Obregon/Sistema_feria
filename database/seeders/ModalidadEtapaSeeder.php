<?php
// database/seeders/ModalidadEtapaSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Etapa;
use App\Models\Modalidad;
use App\Models\Nivel;
use App\Models\TipoInstitucion;

class ModalidadEtapaSeeder extends Seeder {
  public function run(): void {

    TipoInstitucion::firstOrCreate(
      ['nombre' => 'publica'],
      ['activo' => true]
    );

    TipoInstitucion::firstOrCreate(
      ['nombre' => 'privada'],
      ['activo' => true]
    );

    TipoInstitucion::firstOrCreate(
      ['nombre' => 'subvencionada'],
      ['activo' => true]
    );

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
    $sync('Preescolar', [
      'Preescolar regular',
      'Preescolar Centros de educación especial',
    ], [$inst]);

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

    $sync('Secundaria III Ciclo de la Educación General', [
      'Secundaria académica regular','Secundaria académica indígena',
    ], [$inst, $circ, $reg]);
    $sync('Básica y Educación Diversificada', [
      'Secundaria científica y colegios humanísticos',
      'Secundaria técnica',
      'Secundaria Educación Especial (III y IV Ciclos Centros de Educación Especial y Plan Nacional)',
    ], [$inst, $circ, $reg]);

    $sync('Secundaria III Ciclo y Educación Diversificada de la Educación de Personas Jóvenes y Adultas (EPJA)', [
      'Secundaria académica nocturna',
      'II Nivel del Plan de Estudios de Educación de Adultos (CINDEA e IPEC)',
      'III Nivel del Plan de Estudios de Educación de Adultos (CINDEA e IPEC)',
    ], [$inst, $circ, $reg]);
  }
}
