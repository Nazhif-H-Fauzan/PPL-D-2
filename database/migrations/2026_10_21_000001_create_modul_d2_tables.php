<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Contracts Table (ENT-K02-02)
        Schema::create('contracts', function (Blueprint $table) {
            $table->string('contract_id', 50)->primary();
            $table->string('project_id', 50)->index();
            $table->string('contract_number', 50)->unique();
            $table->decimal('contract_value', 18, 2);
            $table->string('created_by', 50);
            $table->timestamps();
        });

        // 2. Termins Table (ENT-K02-03)
        Schema::create('termins', function (Blueprint $table) {
            $table->string('termin_id', 50)->primary();
            $table->string('contract_id', 50);
            $table->unsignedInteger('termin_number');
            $table->decimal('termin_value', 18, 2);
            $table->date('due_date');
            $table->string('status', 30)->default('SIAP_DITAGIH'); // BELUM_SIAP, SIAP_DITAGIH, SUDAH_DITAGIHKAN
            $table->timestamps();

            $table->foreign('contract_id')
                ->references('contract_id')
                ->on('contracts')
                ->onDelete('cascade');
        });

        // 3. Tax Parameters Table
        Schema::create('tax_parameters', function (Blueprint $table) {
            $table->string('tax_param_id', 50)->primary();
            $table->string('contract_id', 50)->nullable();
            $table->decimal('ppn_rate', 5, 2)->default(11.00);
            $table->decimal('pph_rate', 5, 2)->default(2.00);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();

            $table->foreign('contract_id')
                ->references('contract_id')
                ->on('contracts')
                ->onDelete('set null');
        });

        // 4. Invoices Table (ENT-K02-04)
        Schema::create('invoices', function (Blueprint $table) {
            $table->string('invoice_id', 50)->primary();
            $table->string('invoice_number', 50)->unique();
            $table->string('termin_id', 50)->unique();
            $table->string('tax_param_id', 50);
            $table->date('invoice_date');
            $table->decimal('dpp_amount', 18, 2);
            $table->decimal('ppn_amount', 18, 2);
            $table->decimal('pph_amount', 18, 2);
            $table->decimal('total_amount', 18, 2);
            $table->string('status', 30)->default('TERTAGIH'); // TERTAGIH, TERBAYAR
            $table->string('created_by', 50);
            $table->timestamps();

            $table->foreign('termin_id')
                ->references('termin_id')
                ->on('termins')
                ->onDelete('restrict');

            $table->foreign('tax_param_id')
                ->references('tax_param_id')
                ->on('tax_parameters')
                ->onDelete('restrict');
        });

        // 5. Invoice Status Histories Table
        Schema::create('invoice_histories', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_id', 50);
            $table->string('status', 30);
            $table->timestamp('changed_at');
            $table->string('changed_by', 50);
            $table->string('note')->nullable();
            $table->timestamps();

            $table->foreign('invoice_id')
                ->references('invoice_id')
                ->on('invoices')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_histories');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('tax_parameters');
        Schema::dropIfExists('termins');
        Schema::dropIfExists('contracts');
    }
};
