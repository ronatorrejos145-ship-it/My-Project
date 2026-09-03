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
        // Network Towers
        Schema::create('network_towers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('tower_type', 50)->default('ROOFTOP'); // ROOFTOP, MONOPOLE, LATTICE, GUYED, OTHER
            $table->decimal('height_meters', 8, 2)->default(0.00);
            $table->string('owner', 100)->default('COMPANY'); // COMPANY, LEASED, SHARED
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->foreignId('service_area_id')->nullable()->constrained('service_areas')->nullOnDelete();
            $table->string('status', 30)->default('ACTIVE')->index(); // ACTIVE, INACTIVE, MAINTENANCE, PLANNED, DECOMMISSIONED
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Fiber Distribution Points & Splitters
        Schema::create('distribution_points', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('dp_type', 50)->default('FIBER_SPLITTER'); // FIBER_SPLITTER, CABINET, DISTRIBUTION_BOX, POLE, JUNCTION
            $table->integer('capacity')->default(16);
            $table->foreignId('parent_node_id')->nullable()->constrained('network_nodes')->nullOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('status', 30)->default('ACTIVE')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Location Movement History
        Schema::create('location_histories', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 100)->index(); // Customer, NetworkNode, AccessPoint, NetworkDevice, NetworkTower, DistributionPoint
            $table->unsignedBigInteger('entity_id')->index();
            $table->decimal('previous_latitude', 10, 7)->nullable();
            $table->decimal('previous_longitude', 10, 7)->nullable();
            $table->decimal('new_latitude', 10, 7);
            $table->decimal('new_longitude', 10, 7);
            $table->text('reason')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();
        });

        // Enhance service_areas for GeoJSON boundaries and map visualization
        Schema::table('service_areas', function (Blueprint $table) {
            $table->json('boundary_geojson')->nullable()->after('description');
            $table->string('color_code', 20)->default('#3B82F6')->after('boundary_geojson');
            $table->integer('geometry_version')->default(1)->after('color_code');
        });

        // GIS Imports Log
        Schema::create('gis_imports', function (Blueprint $table) {
            $table->id();
            $table->string('original_filename');
            $table->string('file_type', 30)->default('CSV'); // CSV, GEOJSON
            $table->integer('records_processed')->default(0);
            $table->integer('records_imported')->default(0);
            $table->integer('records_failed')->default(0);
            $table->json('error_summary')->nullable();
            $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gis_imports');

        Schema::table('service_areas', function (Blueprint $table) {
            $table->dropColumn(['boundary_geojson', 'color_code', 'geometry_version']);
        });

        Schema::dropIfExists('location_histories');
        Schema::dropIfExists('distribution_points');
        Schema::dropIfExists('network_towers');
    }
};
