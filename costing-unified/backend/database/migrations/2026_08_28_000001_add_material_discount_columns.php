<?php
// FILE PATH: database/migrations/2026_08_28_000001_add_material_discount_columns.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Material discount snapshot columns for the new costing formula:
 *   Customer Item Price = (supplier_unit_cost × 1.5 × qty) − (supplier_discount × qty)
 *
 * NOTE: order_items.supplier_discount is now interpreted as PER UNIT.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->decimal('material_discount', 12, 2)->default(0)->after('unit_price');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('material_discount_total', 12, 2)->default(0)->after('material_total');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn('material_discount');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('material_discount_total');
        });
    }
};
