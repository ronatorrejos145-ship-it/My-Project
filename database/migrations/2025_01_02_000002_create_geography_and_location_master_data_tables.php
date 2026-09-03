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
        // Regions
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('region_number', 20)->nullable();
            $table->timestamps();
        });

        // Provinces
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained('regions')->onDelete('restrict');
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->timestamps();
        });

        // Cities & Municipalities
        Schema::create('cities_municipalities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->constrained('provinces')->onDelete('restrict');
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('type', 30)->default('CITY'); // CITY or MUNICIPALITY
            $table->string('postal_code', 20)->nullable();
            $table->timestamps();
        });

        // Barangays
        Schema::create('barangays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_municipality_id')->constrained('cities_municipalities')->onDelete('restrict');
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('district')->nullable();
            $table->timestamps();
        });

        // Universal Address Table
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('address_type', 50)->default('PRIMARY')->index();
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('house_number', 50)->nullable();
            $table->string('building')->nullable();
            $table->string('unit', 50)->nullable();
            $table->string('street')->nullable();
            $table->string('subdivision')->nullable();
            $table->string('purok', 50)->nullable();
            $table->string('sitio', 50)->nullable();
            $table->foreignId('barangay_id')->nullable()->constrained('barangays')->nullOnDelete();
            $table->foreignId('city_municipality_id')->nullable()->constrained('cities_municipalities')->nullOnDelete();
            $table->foreignId('province_id')->nullable()->constrained('provinces')->nullOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 100)->default('Philippines');
            $table->string('landmark')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('coordinate_accuracy', 8, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Generalized Locations
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location_type', 50)->index(); // CUSTOMER, TOWER, ACCESS_POINT, NANOBOX, ROUTER, NETWORK_NODE, WAREHOUSE, BRANCH, etc.
            $table->foreignId('address_id')->nullable()->constrained('addresses')->nullOnDelete();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('accuracy', 8, 2)->nullable();
            $table->string('landmark')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Service Areas
        Schema::create('service_areas', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->text('description')->nullable();
            $table->string('status', 30)->default('ACTIVE')->index(); // ACTIVE, INACTIVE, PLANNED, RESTRICTED
            $table->string('serviceability_status', 50)->default('SERVICEABLE')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Pivot between Service Areas and Barangays / Municipalities
        Schema::create('service_area_geographic_area', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_area_id')->constrained('service_areas')->onDelete('cascade');
            $table->foreignId('region_id')->nullable()->constrained('regions')->onDelete('cascade');
            $table->foreignId('province_id')->nullable()->constrained('provinces')->onDelete('cascade');
            $table->foreignId('city_municipality_id')->nullable()->constrained('cities_municipalities')->onDelete('cascade');
            $table->foreignId('barangay_id')->nullable()->constrained('barangays')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_area_geographic_area');
        Schema::dropIfExists('service_areas');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('barangays');
        Schema::dropIfExists('cities_municipalities');
        Schema::dropIfExists('provinces');
        Schema::dropIfExists('regions');
    }
};
