<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'customer_group')) {
                $table->string('customer_group', 24)->default('retail')->after('password')->index();
            }

            if (! Schema::hasColumn('users', 'b2b_requested_at')) {
                $table->timestamp('b2b_requested_at')->nullable()->after('customer_group');
            }

            if (! Schema::hasColumn('users', 'b2b_approved_at')) {
                $table->timestamp('b2b_approved_at')->nullable()->after('b2b_requested_at');
            }

            if (! Schema::hasColumn('users', 'b2b_rejected_at')) {
                $table->timestamp('b2b_rejected_at')->nullable()->after('b2b_approved_at');
            }

            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('b2b_rejected_at');
            }

            if (! Schema::hasColumn('users', 'mobile')) {
                $table->string('mobile')->nullable()->after('phone');
            }

            if (! Schema::hasColumn('users', 'company_name')) {
                $table->string('company_name')->nullable()->after('mobile');
            }

            if (! Schema::hasColumn('users', 'vat_number')) {
                $table->string('vat_number')->nullable()->after('company_name');
            }

            if (! Schema::hasColumn('users', 'occupation')) {
                $table->string('occupation')->nullable()->after('vat_number');
            }

            if (! Schema::hasColumn('users', 'company_address')) {
                $table->string('company_address')->nullable()->after('occupation');
            }

            if (! Schema::hasColumn('users', 'city')) {
                $table->string('city')->nullable()->after('company_address');
            }

            if (! Schema::hasColumn('users', 'postcode')) {
                $table->string('postcode', 24)->nullable()->after('city');
            }

            if (! Schema::hasColumn('users', 'customer_notes')) {
                $table->text('customer_notes')->nullable()->after('postcode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            foreach ([
                'customer_group',
                'b2b_requested_at',
                'b2b_approved_at',
                'b2b_rejected_at',
                'phone',
                'mobile',
                'company_name',
                'vat_number',
                'occupation',
                'company_address',
                'city',
                'postcode',
                'customer_notes',
            ] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
