<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
     public function up()
    {
        Schema::table('pesanan', function (Blueprint $table) {
            // Hapus foreign key lama (jika ada)
            $table->dropForeign(['paket_id']);

            // Tambahkan foreign key baru dengan onDelete cascade
            $table->foreign('paket_id')
                ->references('id')->on('paket')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropForeign(['paket_id']);

            // Kembalikan foreign key tanpa cascade
            $table->foreign('paket_id')
                ->references('id')->on('paket')
                ->onDelete('restrict'); // atau sesuai kebutuhan awal
        });
    }
};