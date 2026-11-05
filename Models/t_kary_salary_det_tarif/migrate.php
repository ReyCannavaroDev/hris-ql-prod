<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class tkarysalarydettarif extends Migration
{
    protected $tableName = "t_kary_salary_det_tarif";

    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->id()->from(1);

            $table->bigInteger('m_kary')->comment('{"src" : "m_kary.id"}');
            $table->date('date');
            $table->bigInteger('m_tarif_group_id')->comment('{"src" : "m_tarif_group.id"}')->nullable();
            $table->bigInteger('m_tarif_id')->comment('{"src" : "m_tarif.id"}');
            $table->decimal('tarif',18,2);
            $table->string('tarif_desc');
            $table->decimal('qty',10,2);
            $table->decimal('subtotal',18,2);
            $table->string('keterangan')->nullable();
            $table->bigInteger('pic_id')->comment('{"src" : "default_users.id"}');
            $table->bigInteger('creator_id')->nullable();
            $table->bigInteger('last_editor_id')->nullable();

            // $table->integer('creator_id')->nullable();
            // $table->integer('last_editor_id')->nullable();
            $table->timestamps();
        });

        table_config($this->tableName, [
            "guarded"       => ["id"],
            "required"      => [],
            "!createable"   => ["id","created_at","updated_at"],
            "!updateable"   => ["id","created_at","updated_at"],
            "searchable"    => "all",
            "deleteable"    => "true",
            "deleteOnUse"   => "false",
            "extendable"    => "false",
            "casts"     => [
                'created_at' => 'datetime:d/m/Y H:i',
                'updated_at' => 'datetime:d/m/Y H:i'
            ]
        ]);

        // if( $data = \Cache::pull($this->tableName) ){
        //     $fixedData = json_decode( json_encode( $data ), true );
        //     \DB::table($this->tableName)->insert( $fixedData );
        // }
    }
    public function down()
    {
        // if( Schema::hasTable($this->tableName) ){
        //     \Cache::put($this->tableName, \DB::table($this->tableName)->get(), 60*30 );
        // }
        Schema::dropIfExists($this->tableName);
    }
}