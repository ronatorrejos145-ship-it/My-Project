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
        // Network Nodes
        Schema::create('network_nodes', function (Blueprint $table) {
            $table->id();
            $table->string('node_code', 50)->unique();
            $table->string('name');
            $table->string('node_type', 50)->default('ACCESS')->index(); // CORE, DISTRIBUTION, ACCESS, RELAY, POP, TOWER, OTHER
            $table->string('status', 30)->default('ACTIVE')->index();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('service_area_id')->nullable()->constrained('service_areas')->nullOnDelete();
            $table->foreignId('parent_node_id')->nullable()->constrained('network_nodes')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('coordinate_accuracy', 8, 2)->nullable();
            $table->text('address')->nullable();
            $table->text('description')->nullable();
            $table->date('installation_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Access Points
        Schema::create('access_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('node_id')->constrained('network_nodes')->onDelete('cascade');
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->string('technology', 50)->nullable(); // 2.4GHz, 5GHz, 60GHz, Fiber, GPON, LTE
            $table->string('frequency', 50)->nullable();
            $table->string('ssid')->nullable();
            $table->text('coverage_notes')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('status', 30)->default('ACTIVE')->index();
            $table->integer('capacity')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Network Devices
        Schema::create('network_devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_code', 50)->unique();
            $table->string('device_name');
            $table->string('device_type', 50)->index(); // MIKROTIK, ROUTER, SWITCH, NANOBOX, ONU, MODEM, GATEWAY, OTHER
            $table->string('hostname')->nullable();
            $table->string('management_ip', 50)->nullable()->index();
            $table->string('mac_address', 50)->nullable()->index();
            $table->string('serial_number', 100)->nullable()->index();
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->string('firmware_version')->nullable();
            $table->foreignId('node_id')->nullable()->constrained('network_nodes')->nullOnDelete();
            $table->foreignId('parent_device_id')->nullable()->constrained('network_devices')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('capacity')->default(0);
            $table->string('status', 30)->default('ACTIVE')->index();
            $table->date('installation_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Add network_device_id foreign key to access_points
        Schema::table('access_points', function (Blueprint $table) {
            $table->foreignId('network_device_id')->nullable()->after('node_id')->constrained('network_devices')->nullOnDelete();
        });

        // Network Interfaces
        Schema::create('network_interfaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained('network_devices')->onDelete('cascade');
            $table->string('interface_name');
            $table->string('interface_type', 50)->default('ETHERNET'); // ETHERNET, WIRELESS, FIBER, VLAN, BRIDGE, PPPOE, OTHER
            $table->string('mac_address', 50)->nullable();
            $table->string('ip_address', 50)->nullable();
            $table->string('vlan', 50)->nullable();
            $table->string('speed', 50)->nullable(); // 100Mbps, 1Gbps, 10Gbps, etc.
            $table->string('status', 30)->default('ACTIVE')->index();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('network_interfaces');
        Schema::table('access_points', function (Blueprint $table) {
            $table->dropForeign(['network_device_id']);
            $table->dropColumn('network_device_id');
        });
        Schema::dropIfExists('network_devices');
        Schema::dropIfExists('access_points');
        Schema::dropIfExists('network_nodes');
    }
};
