<?php

namespace Database\Seeders;

use App\Enums\BusinessStatus;
use App\Enums\DriverAvailability;
use App\Enums\InventoryMovementType;
use App\Enums\OrderStatus;
use App\Enums\PackageScanType;
use App\Enums\PackageStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Business;
use App\Models\DeliveryDriver;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use App\Models\Package;
use App\Models\PackageScan;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. SuperAdmin
        $superadmin = User::create([
            'name' => 'Super Administrador FastLogi',
            'email' => 'super@fastlogi.test',
            'phone' => '3000000000',
            'password' => Hash::make('superadmin'),
            'role' => UserRole::Superadmin,
            'status' => UserStatus::Active,
        ]);

        // 2. Negocio 1: Restaurante El Leñador (Comida)
        $business1 = Business::create([
            'name' => 'Restaurante El Leñador',
            'address' => 'Calle 93 # 14-20, Chicó, Bogotá',
            'phone' => '3101234567',
            'status' => BusinessStatus::Active,
            'suspended_at' => null,
            'inactivity_threshold_minutes' => 15,
        ]);

        $owner1 = User::create([
            'business_id' => $business1->id,
            'name' => 'Don Antonio Leñador',
            'email' => 'owner@fastlogi.test',
            'phone' => '3101234567',
            'password' => Hash::make('password123'),
            'role' => UserRole::Owner,
            'status' => UserStatus::Active,
        ]);

        // 3. Negocio 2: Calzado San Victorino (Calzado)
        $business2 = Business::create([
            'name' => 'Calzado San Victorino',
            'address' => 'Carrera 10 # 12-45, San Victorino, Bogotá',
            'phone' => '3157891234',
            'status' => BusinessStatus::Active,
            'suspended_at' => null,
            'inactivity_threshold_minutes' => 15,
        ]);

        $owner2 = User::create([
            'business_id' => $business2->id,
            'name' => 'Doña Gloria Calzado',
            'email' => 'calzado@fastlogi.test',
            'phone' => '3157891234',
            'password' => Hash::make('password123'),
            'role' => UserRole::Owner,
            'status' => UserStatus::Active,
        ]);

        // 4. Repartidores Negocio 1 (3 repartidores)
        $driverUser1 = User::create([
            'business_id' => $business1->id,
            'name' => 'Carlos Gómez',
            'email' => 'driver1@fastlogi.test',
            'phone' => '3101112233', // Credencial de prueba del SRS
            'password' => Hash::make('driverpass'),
            'role' => UserRole::Driver,
            'status' => UserStatus::Active,
        ]);
        $driver1 = DeliveryDriver::create([
            'user_id' => $driverUser1->id,
            'business_id' => $business1->id,
            'availability' => DriverAvailability::Available,
            'max_active_orders' => 3,
            'latitude' => 4.6781200,
            'longitude' => -74.0534500,
            'last_seen_at' => now(),
        ]);

        $driverUser2 = User::create([
            'business_id' => $business1->id,
            'name' => 'Andrés Martínez',
            'email' => 'driver2@fastlogi.test',
            'phone' => '3102223344',
            'password' => Hash::make('driverpass'),
            'role' => UserRole::Driver,
            'status' => UserStatus::Active,
        ]);
        $driver2 = DeliveryDriver::create([
            'user_id' => $driverUser2->id,
            'business_id' => $business1->id,
            'availability' => DriverAvailability::Busy,
            'max_active_orders' => 3,
            'latitude' => 4.6854100,
            'longitude' => -74.0489100,
            'last_seen_at' => now()->subMinutes(2),
        ]);

        $driverUser3 = User::create([
            'business_id' => $business1->id,
            'name' => 'Laura Sánchez',
            'email' => 'driver3@fastlogi.test',
            'phone' => '3103334455',
            'password' => Hash::make('driverpass'),
            'role' => UserRole::Driver,
            'status' => UserStatus::Active,
        ]);
        $driver3 = DeliveryDriver::create([
            'user_id' => $driverUser3->id,
            'business_id' => $business1->id,
            'availability' => DriverAvailability::Offline,
            'max_active_orders' => 3,
            'latitude' => null,
            'longitude' => null,
            'last_seen_at' => now()->subHours(3),
        ]);

        // 5. Repartidores Negocio 2 (3 repartidores)
        $driverUser4 = User::create([
            'business_id' => $business2->id,
            'name' => 'Felipe Torres',
            'email' => 'driver4@fastlogi.test',
            'phone' => '3114445566',
            'password' => Hash::make('driverpass'),
            'role' => UserRole::Driver,
            'status' => UserStatus::Active,
        ]);
        $driver4 = DeliveryDriver::create([
            'user_id' => $driverUser4->id,
            'business_id' => $business2->id,
            'availability' => DriverAvailability::Available,
            'max_active_orders' => 3,
            'latitude' => 4.6012300,
            'longitude' => -74.0754100,
            'last_seen_at' => now(),
        ]);

        $driverUser5 = User::create([
            'business_id' => $business2->id,
            'name' => 'Diana Rojas',
            'email' => 'driver5@fastlogi.test',
            'phone' => '3115556677',
            'password' => Hash::make('driverpass'),
            'role' => UserRole::Driver,
            'status' => UserStatus::Active,
        ]);
        $driver5 = DeliveryDriver::create([
            'user_id' => $driverUser5->id,
            'business_id' => $business2->id,
            'availability' => DriverAvailability::Busy,
            'max_active_orders' => 3,
            'latitude' => 4.6045600,
            'longitude' => -74.0721000,
            'last_seen_at' => now()->subMinutes(5),
        ]);

        $driverUser6 = User::create([
            'business_id' => $business2->id,
            'name' => 'Santiago Ruiz',
            'email' => 'driver6@fastlogi.test',
            'phone' => '3116667788',
            'password' => Hash::make('driverpass'),
            'role' => UserRole::Driver,
            'status' => UserStatus::Active,
        ]);
        $driver6 = DeliveryDriver::create([
            'user_id' => $driverUser6->id,
            'business_id' => $business2->id,
            'availability' => DriverAvailability::Offline,
            'max_active_orders' => 3,
            'latitude' => null,
            'longitude' => null,
            'last_seen_at' => now()->subDays(1),
        ]);

        // 6. Productos Negocio 1 (Comida - 8 productos)
        $p1 = Product::create([
            'business_id' => $business1->id,
            'name' => 'Bandeja Paisa Tradicional',
            'barcode' => '7701001000011',
            'price' => 32000,
            'stock' => 20,
            'min_stock' => 5,
            'unit' => 'plato',
            'low_stock_alerted' => false,
        ]);
        $p2 = Product::create([
            'business_id' => $business1->id,
            'name' => 'Ajiaco Santafereño con Pollo',
            'barcode' => '7701001000028',
            'price' => 28000,
            'stock' => 15,
            'min_stock' => 5,
            'unit' => 'plato',
            'low_stock_alerted' => false,
        ]);
        $p3 = Product::create([
            'business_id' => $business1->id,
            'name' => 'Lomo al Trapo (500g)',
            'barcode' => '7701001000035',
            'price' => 45000,
            'stock' => 8,
            'min_stock' => 3,
            'unit' => 'porción',
            'low_stock_alerted' => false,
        ]);
        $p4 = Product::create([
            'business_id' => $business1->id,
            'name' => 'Costillas BBQ Ahumadas',
            'barcode' => '7701001000042',
            'price' => 38000,
            'stock' => 3, // Bajo el mínimo
            'min_stock' => 5,
            'unit' => 'porción',
            'low_stock_alerted' => true,
        ]);
        $p5 = Product::create([
            'business_id' => $business1->id,
            'name' => 'Hamburguesa Artesanal Doble Carne',
            'barcode' => '7701001000059',
            'price' => 26000,
            'stock' => 0, // Agotado
            'min_stock' => 5,
            'unit' => 'unidad',
            'low_stock_alerted' => true,
        ]);
        $p6 = Product::create([
            'business_id' => $business1->id,
            'name' => 'Picada Criolla para 4 Personas',
            'barcode' => '7701001000066',
            'price' => 65000,
            'stock' => 6,
            'min_stock' => 2,
            'unit' => 'plato',
            'low_stock_alerted' => false,
        ]);
        $p7 = Product::create([
            'business_id' => $business1->id,
            'name' => 'Jugo Natural en Agua (Mora/Maracuyá)',
            'barcode' => '7701001000073',
            'price' => 7000,
            'stock' => 30,
            'min_stock' => 10,
            'unit' => 'vaso',
            'low_stock_alerted' => false,
        ]);
        $p8 = Product::create([
            'business_id' => $business1->id,
            'name' => 'Postre de Natas Típico',
            'barcode' => '7701001000080',
            'price' => 9500,
            'stock' => 2, // Bajo el mínimo
            'min_stock' => 5,
            'unit' => 'porción',
            'low_stock_alerted' => true,
        ]);

        // 7. Productos Negocio 2 (Calzado - 7 productos)
        $p9 = Product::create([
            'business_id' => $business2->id,
            'name' => 'Zapato Formal en Cuero Oxford Talla 40',
            'barcode' => '7702002000010',
            'price' => 145000,
            'stock' => 12,
            'min_stock' => 3,
            'unit' => 'par',
            'low_stock_alerted' => false,
        ]);
        $p10 = Product::create([
            'business_id' => $business2->id,
            'name' => 'Mocasín Clásico Negro Talla 39',
            'barcode' => '7702002000027',
            'price' => 120000,
            'stock' => 10,
            'min_stock' => 3,
            'unit' => 'par',
            'low_stock_alerted' => false,
        ]);
        $p11 = Product::create([
            'business_id' => $business2->id,
            'name' => 'Bota de Seguridad Dieléctrica Talla 41',
            'barcode' => '7702002000034',
            'price' => 165000,
            'stock' => 2, // Bajo el mínimo
            'min_stock' => 5,
            'unit' => 'par',
            'low_stock_alerted' => true,
        ]);
        $p12 = Product::create([
            'business_id' => $business2->id,
            'name' => 'Tenis Deportivo Urbano Blanco Talla 38',
            'barcode' => '7702002000041',
            'price' => 95000,
            'stock' => 0, // Agotado
            'min_stock' => 4,
            'unit' => 'par',
            'low_stock_alerted' => true,
        ]);
        $p13 = Product::create([
            'business_id' => $business2->id,
            'name' => 'Sandalia Casual Cuero Dama Talla 37',
            'barcode' => '7702002000058',
            'price' => 75000,
            'stock' => 18,
            'min_stock' => 4,
            'unit' => 'par',
            'low_stock_alerted' => false,
        ]);
        $p14 = Product::create([
            'business_id' => $business2->id,
            'name' => 'Botín Chelsea en Gamuza Miel Talla 41',
            'barcode' => '7702002000065',
            'price' => 135000,
            'stock' => 5,
            'min_stock' => 2,
            'unit' => 'par',
            'low_stock_alerted' => false,
        ]);
        $p15 = Product::create([
            'business_id' => $business2->id,
            'name' => 'Calzador Metálico Profesional 40cm',
            'barcode' => '7702002000072',
            'price' => 15000,
            'stock' => 25,
            'min_stock' => 5,
            'unit' => 'unidad',
            'low_stock_alerted' => false,
        ]);

        // 8. Pedidos de Demostración - Negocio 1 (Comida)
        // Pedido 1: Pending (Reserva activa)
        $order1 = Order::create([
            'business_id' => $business1->id,
            'customer_name' => 'María Camila Rodríguez',
            'customer_phone' => '3142223344',
            'delivery_address' => 'Carrera 15 # 93-40 Apto 402, Chicó',
            'payment_method' => 'Contra Entrega (Efectivo)',
            'total' => 60000,
            'status' => OrderStatus::Pending,
            'requires_traceability' => false,
        ]);
        OrderItem::create(['order_id' => $order1->id, 'product_id' => $p1->id, 'quantity' => 1, 'unit_price' => 32000, 'subtotal' => 32000]);
        OrderItem::create(['order_id' => $order1->id, 'product_id' => $p2->id, 'quantity' => 1, 'unit_price' => 28000, 'subtotal' => 28000]);
        InventoryMovement::create(['product_id' => $p1->id, 'order_id' => $order1->id, 'type' => InventoryMovementType::Reserve, 'quantity' => -1, 'user_id' => $owner1->id]);
        InventoryMovement::create(['product_id' => $p2->id, 'order_id' => $order1->id, 'type' => InventoryMovementType::Reserve, 'quantity' => -1, 'user_id' => $owner1->id]);
        OrderStatusLog::create(['order_id' => $order1->id, 'from' => '', 'to' => OrderStatus::Pending->value, 'user_id' => $owner1->id, 'reason' => 'Pedido creado por el dueño']);

        // Pedido 2: Offered (Oferta activa para Driver 1)
        $order2 = Order::create([
            'business_id' => $business1->id,
            'customer_name' => 'Julián David Morales',
            'customer_phone' => '3185556677',
            'delivery_address' => 'Calle 94 # 11-30, Chicó Norte',
            'payment_method' => 'Nequi / Daviplata',
            'total' => 45000,
            'status' => OrderStatus::Offered,
            'requires_traceability' => true,
            'delivery_driver_id' => $driver1->id,
            'offered_at' => now(),
            'offer_expires_at' => now()->addSeconds(90),
        ]);
        OrderItem::create(['order_id' => $order2->id, 'product_id' => $p3->id, 'quantity' => 1, 'unit_price' => 45000, 'subtotal' => 45000]);
        InventoryMovement::create(['product_id' => $p3->id, 'order_id' => $order2->id, 'type' => InventoryMovementType::Reserve, 'quantity' => -1, 'user_id' => $owner1->id]);
        Package::create(['order_id' => $order2->id, 'tracking_code' => 'PKG-1042-BOG', 'status' => PackageStatus::Pending]);
        OrderStatusLog::create(['order_id' => $order2->id, 'from' => OrderStatus::Pending->value, 'to' => OrderStatus::Offered->value, 'user_id' => $owner1->id, 'reason' => 'Oferta enviada a Carlos Gómez']);

        // Pedido 3: Assigned (Con trazabilidad, Driver 2 asignado)
        $order3 = Order::create([
            'business_id' => $business1->id,
            'customer_name' => 'Mariana Gómez',
            'customer_phone' => '3104567890',
            'delivery_address' => 'Cra. 15 # 85-12, Antiguo Country',
            'payment_method' => 'Transferencia Bancaria',
            'total' => 64000,
            'status' => OrderStatus::Assigned,
            'requires_traceability' => true,
            'delivery_driver_id' => $driver2->id,
            'offered_at' => now()->subMinutes(10),
            'offer_expires_at' => now()->subMinutes(8),
        ]);
        OrderItem::create(['order_id' => $order3->id, 'product_id' => $p1->id, 'quantity' => 2, 'unit_price' => 32000, 'subtotal' => 64000]);
        InventoryMovement::create(['product_id' => $p1->id, 'order_id' => $order3->id, 'type' => InventoryMovementType::Reserve, 'quantity' => -2, 'user_id' => $owner1->id]);
        Package::create(['order_id' => $order3->id, 'tracking_code' => 'PKG-9821-BOG', 'status' => PackageStatus::Pending]);
        OrderStatusLog::create(['order_id' => $order3->id, 'from' => OrderStatus::Offered->value, 'to' => OrderStatus::Assigned->value, 'user_id' => $driverUser2->id, 'reason' => 'Aceptado por Andrés Martínez']);

        // Pedido 4: In Transit (Con trazabilidad, Driver 2 en ruta con escaneo de recogida)
        $order4 = Order::create([
            'business_id' => $business1->id,
            'customer_name' => 'Jorge Enrique Castro',
            'customer_phone' => '3207778899',
            'delivery_address' => 'Calle 100 # 19-61 Of 501',
            'payment_method' => 'Contra Entrega (Efectivo)',
            'total' => 65000,
            'status' => OrderStatus::InTransit,
            'requires_traceability' => true,
            'delivery_driver_id' => $driver2->id,
            'offered_at' => now()->subMinutes(25),
            'offer_expires_at' => now()->subMinutes(23),
        ]);
        OrderItem::create(['order_id' => $order4->id, 'product_id' => $p6->id, 'quantity' => 1, 'unit_price' => 65000, 'subtotal' => 65000]);
        InventoryMovement::create(['product_id' => $p6->id, 'order_id' => $order4->id, 'type' => InventoryMovementType::Reserve, 'quantity' => -1, 'user_id' => $owner1->id]);
        $pkg4 = Package::create(['order_id' => $order4->id, 'tracking_code' => 'PKG-8742-BOG', 'status' => PackageStatus::PickedUp]);
        PackageScan::create([
            'package_id' => $pkg4->id,
            'type' => PackageScanType::Pickup,
            'user_id' => $driverUser2->id,
            'latitude' => 4.6781200,
            'longitude' => -74.0534500,
            'scanned_at' => now()->subMinutes(15),
        ]);
        OrderStatusLog::create(['order_id' => $order4->id, 'from' => OrderStatus::Assigned->value, 'to' => OrderStatus::InTransit->value, 'user_id' => $driverUser2->id, 'reason' => 'Paquete recogido en restaurante']);

        // Pedido 5: Delivered (Con trazabilidad, completado)
        $order5 = Order::create([
            'business_id' => $business1->id,
            'customer_name' => 'Catalina Peñuela',
            'customer_phone' => '3168889900',
            'delivery_address' => 'Carrera 11 # 82-01',
            'payment_method' => 'Nequi / Daviplata',
            'total' => 32000,
            'status' => OrderStatus::Delivered,
            'requires_traceability' => true,
            'delivery_driver_id' => $driver2->id,
            'offered_at' => now()->subHours(2),
            'offer_expires_at' => now()->subHours(2)->addSeconds(90),
        ]);
        OrderItem::create(['order_id' => $order5->id, 'product_id' => $p1->id, 'quantity' => 1, 'unit_price' => 32000, 'subtotal' => 32000]);
        $pkg5 = Package::create(['order_id' => $order5->id, 'tracking_code' => 'PKG-7120-BOG', 'status' => PackageStatus::Delivered]);
        PackageScan::create([
            'package_id' => $pkg5->id,
            'type' => PackageScanType::Pickup,
            'user_id' => $driverUser2->id,
            'latitude' => 4.6781200,
            'longitude' => -74.0534500,
            'scanned_at' => now()->subHours(2)->addMinutes(10),
        ]);
        PackageScan::create([
            'package_id' => $pkg5->id,
            'type' => PackageScanType::Delivery,
            'user_id' => $driverUser2->id,
            'latitude' => 4.6678900,
            'longitude' => -74.0567000,
            'scanned_at' => now()->subHours(1)->addMinutes(35),
        ]);
        OrderStatusLog::create(['order_id' => $order5->id, 'from' => OrderStatus::InTransit->value, 'to' => OrderStatus::Delivered->value, 'user_id' => $driverUser2->id, 'reason' => 'Entrega confirmada']);

        // Pedido 6: Cancelled (Libera stock)
        $order6 = Order::create([
            'business_id' => $business1->id,
            'customer_name' => 'Mauricio Cárdenas',
            'customer_phone' => '3123334455',
            'delivery_address' => 'Calle 92 # 16-10',
            'payment_method' => 'Contra Entrega (Efectivo)',
            'total' => 28000,
            'status' => OrderStatus::Cancelled,
            'requires_traceability' => false,
            'cancel_reason' => 'Cliente canceló el pedido por demora en la preparación',
        ]);
        OrderItem::create(['order_id' => $order6->id, 'product_id' => $p2->id, 'quantity' => 1, 'unit_price' => 28000, 'subtotal' => 28000]);
        OrderStatusLog::create(['order_id' => $order6->id, 'from' => OrderStatus::Pending->value, 'to' => OrderStatus::Cancelled->value, 'user_id' => $owner1->id, 'reason' => 'Cancelación solicitada por cliente']);

        // Pedido 7: Incident (Problema durante entrega)
        $order7 = Order::create([
            'business_id' => $business1->id,
            'customer_name' => 'Patricia Benavides',
            'customer_phone' => '3194445566',
            'delivery_address' => 'Transversal 23 # 95-20',
            'payment_method' => 'Contra Entrega (Efectivo)',
            'total' => 38000,
            'status' => OrderStatus::Incident,
            'requires_traceability' => true,
            'delivery_driver_id' => $driver1->id,
            'cancel_reason' => 'Dirección incompleta y cliente no responde al teléfono',
        ]);
        OrderItem::create(['order_id' => $order7->id, 'product_id' => $p4->id, 'quantity' => 1, 'unit_price' => 38000, 'subtotal' => 38000]);
        $pkg7 = Package::create(['order_id' => $order7->id, 'tracking_code' => 'PKG-6540-BOG', 'status' => PackageStatus::PickedUp]);
        PackageScan::create([
            'package_id' => $pkg7->id,
            'type' => PackageScanType::Pickup,
            'user_id' => $driverUser1->id,
            'latitude' => 4.6781200,
            'longitude' => -74.0534500,
            'scanned_at' => now()->subMinutes(45),
        ]);
        OrderStatusLog::create(['order_id' => $order7->id, 'from' => OrderStatus::InTransit->value, 'to' => OrderStatus::Incident->value, 'user_id' => $driverUser1->id, 'reason' => 'Cliente no atiende en portería']);

        // 9. Pedidos de Demostración - Negocio 2 (Calzado)
        $order8 = Order::create([
            'business_id' => $business2->id,
            'customer_name' => 'Gonzalo Echeverry',
            'customer_phone' => '3171112233',
            'delivery_address' => 'Carrera 7 # 19-28 Of 304, Centro',
            'payment_method' => 'Transferencia Bancaria',
            'total' => 145000,
            'status' => OrderStatus::InTransit,
            'requires_traceability' => true,
            'delivery_driver_id' => $driver5->id,
            'offered_at' => now()->subMinutes(30),
            'offer_expires_at' => now()->subMinutes(28),
        ]);
        OrderItem::create(['order_id' => $order8->id, 'product_id' => $p9->id, 'quantity' => 1, 'unit_price' => 145000, 'subtotal' => 145000]);
        $pkg8 = Package::create(['order_id' => $order8->id, 'tracking_code' => 'PKG-CALZ-001', 'status' => PackageStatus::PickedUp]);
        PackageScan::create([
            'package_id' => $pkg8->id,
            'type' => PackageScanType::Pickup,
            'user_id' => $driverUser5->id,
            'latitude' => 4.6045600,
            'longitude' => -74.0721000,
            'scanned_at' => now()->subMinutes(20),
        ]);
        OrderStatusLog::create(['order_id' => $order8->id, 'from' => OrderStatus::Assigned->value, 'to' => OrderStatus::InTransit->value, 'user_id' => $driverUser5->id, 'reason' => 'Zapatos recogidos en bodega']);
    }
}
