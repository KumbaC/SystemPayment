<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Alimentos',
                'description' => 'Productos alimenticios de consumo diario.',
                'active' => true,
            ],
            [
                'name' => 'Bebidas',
                'description' => 'Refrescos, jugos, agua y malta.',
                'active' => true,
            ],
            [
                'name' => 'Limpieza',
                'description' => 'Productos para higiene y limpieza del hogar.',
                'active' => true,
            ],
            [
                'name' => 'Cuidado Personal',
                'description' => 'Aseo personal y cuidado diario.',
                'active' => true,
            ],
            [
                'name' => 'Ferreteria',
                'description' => 'Herramientas e insumos para mantenimiento.',
                'active' => true,
            ],
            [
                'name' => 'Papeleria',
                'description' => 'Articulos de oficina y escolares.',
                'active' => true,
            ],
            [
                'name' => 'Mascotas',
                'description' => 'Alimentos y accesorios para mascotas.',
                'active' => true,
            ],
            [
                'name' => 'Confiteria',
                'description' => 'Dulces, galletas y snacks.',
                'active' => true,
            ],
        ];

        foreach ($categories as $categoryData) {
            Category::query()->updateOrCreate(
                ['name' => $categoryData['name']],
                [
                    'description' => $categoryData['description'],
                    'active' => $categoryData['active'],
                ]
            );
        }

        $categoryByName = Category::query()->pluck('id', 'name');

        $products = [
            [
                'category' => 'Alimentos',
                'sku' => 'VE-ALI-001',
                'name' => 'Harina PAN 1kg',
                'description' => 'Harina de maiz precocida.',
                'cost_usd' => 1.05,
                'price_usd' => 1.45,
                'stock' => 150,
                'min_stock' => 25,
                'unit' => 'und',
                'active' => true,
                'has_vat' => true,
            ],
            [
                'category' => 'Alimentos',
                'sku' => 'VE-ALI-002',
                'name' => 'Arroz Mary 1kg',
                'description' => 'Arroz blanco tipo I.',
                'cost_usd' => 1.10,
                'price_usd' => 1.55,
                'stock' => 120,
                'min_stock' => 20,
                'unit' => 'und',
                'active' => true,
                'has_vat' => true,
            ],
            [
                'category' => 'Alimentos',
                'sku' => 'VE-ALI-003',
                'name' => 'Pasta Primor Tornillo 500g',
                'description' => 'Pasta corta de trigo duro.',
                'cost_usd' => 0.65,
                'price_usd' => 0.95,
                'stock' => 180,
                'min_stock' => 30,
                'unit' => 'und',
                'active' => true,
                'has_vat' => true,
            ],
            [
                'category' => 'Bebidas',
                'sku' => 'VE-BEB-001',
                'name' => 'Malta Polar 355ml',
                'description' => 'Bebida no alcoholica malteada.',
                'cost_usd' => 0.45,
                'price_usd' => 0.75,
                'stock' => 240,
                'min_stock' => 40,
                'unit' => 'und',
                'active' => true,
                'has_vat' => true,
            ],
            [
                'category' => 'Bebidas',
                'sku' => 'VE-BEB-002',
                'name' => 'Refresco 2L Cola',
                'description' => 'Refresco sabor cola presentacion familiar.',
                'cost_usd' => 1.20,
                'price_usd' => 1.85,
                'stock' => 90,
                'min_stock' => 18,
                'unit' => 'und',
                'active' => true,
                'has_vat' => true,
            ],
            [
                'category' => 'Bebidas',
                'sku' => 'VE-BEB-003',
                'name' => 'Agua Mineral 600ml',
                'description' => 'Agua mineral sin gas.',
                'cost_usd' => 0.25,
                'price_usd' => 0.45,
                'stock' => 300,
                'min_stock' => 50,
                'unit' => 'und',
                'active' => true,
                'has_vat' => true,
            ],
            [
                'category' => 'Limpieza',
                'sku' => 'VE-LIM-001',
                'name' => 'Detergente en Polvo 1kg',
                'description' => 'Detergente multiuso para ropa.',
                'cost_usd' => 2.10,
                'price_usd' => 2.95,
                'stock' => 70,
                'min_stock' => 12,
                'unit' => 'und',
                'active' => true,
                'has_vat' => true,
            ],
            [
                'category' => 'Limpieza',
                'sku' => 'VE-LIM-002',
                'name' => 'Cloro 1L',
                'description' => 'Desinfectante para superficies.',
                'cost_usd' => 0.70,
                'price_usd' => 1.05,
                'stock' => 110,
                'min_stock' => 20,
                'unit' => 'und',
                'active' => true,
                'has_vat' => true,
            ],
            [
                'category' => 'Cuidado Personal',
                'sku' => 'VE-CUI-001',
                'name' => 'Jabon de Bano 90g',
                'description' => 'Jabon corporal antibacterial.',
                'cost_usd' => 0.40,
                'price_usd' => 0.65,
                'stock' => 210,
                'min_stock' => 35,
                'unit' => 'und',
                'active' => true,
                'has_vat' => true,
            ],
            [
                'category' => 'Cuidado Personal',
                'sku' => 'VE-CUI-002',
                'name' => 'Champu Familiar 750ml',
                'description' => 'Champu para todo tipo de cabello.',
                'cost_usd' => 2.40,
                'price_usd' => 3.25,
                'stock' => 60,
                'min_stock' => 10,
                'unit' => 'und',
                'active' => true,
                'has_vat' => true,
            ],
            [
                'category' => 'Ferreteria',
                'sku' => 'VE-FER-001',
                'name' => 'Cinta Aislante Negra',
                'description' => 'Cinta aislante uso electrico.',
                'cost_usd' => 0.35,
                'price_usd' => 0.65,
                'stock' => 140,
                'min_stock' => 25,
                'unit' => 'und',
                'active' => true,
                'has_vat' => true,
            ],
            [
                'category' => 'Ferreteria',
                'sku' => 'VE-FER-002',
                'name' => 'Bombillo LED 9W',
                'description' => 'Bombillo LED luz blanca 110V.',
                'cost_usd' => 1.10,
                'price_usd' => 1.75,
                'stock' => 95,
                'min_stock' => 15,
                'unit' => 'und',
                'active' => true,
                'has_vat' => true,
            ],
            [
                'category' => 'Papeleria',
                'sku' => 'VE-PAP-001',
                'name' => 'Resma Carta 500 Hojas',
                'description' => 'Papel bond tamano carta.',
                'cost_usd' => 3.60,
                'price_usd' => 4.95,
                'stock' => 50,
                'min_stock' => 8,
                'unit' => 'und',
                'active' => true,
                'has_vat' => true,
            ],
            [
                'category' => 'Papeleria',
                'sku' => 'VE-PAP-002',
                'name' => 'Cuaderno Empastado 100H',
                'description' => 'Cuaderno rayado escolar.',
                'cost_usd' => 1.00,
                'price_usd' => 1.55,
                'stock' => 130,
                'min_stock' => 20,
                'unit' => 'und',
                'active' => true,
                'has_vat' => true,
            ],
            [
                'category' => 'Mascotas',
                'sku' => 'VE-MAS-001',
                'name' => 'Alimento Canino 4kg',
                'description' => 'Alimento balanceado para perros adultos.',
                'cost_usd' => 8.50,
                'price_usd' => 10.95,
                'stock' => 35,
                'min_stock' => 6,
                'unit' => 'und',
                'active' => true,
                'has_vat' => true,
            ],
            [
                'category' => 'Confiteria',
                'sku' => 'VE-CON-001',
                'name' => 'Galletas Maria 200g',
                'description' => 'Galletas dulces tipo maria.',
                'cost_usd' => 0.55,
                'price_usd' => 0.85,
                'stock' => 170,
                'min_stock' => 28,
                'unit' => 'und',
                'active' => true,
                'has_vat' => true,
            ],
            [
                'category' => 'Confiteria',
                'sku' => 'VE-CON-002',
                'name' => 'Chocolate de Leche 40g',
                'description' => 'Barra de chocolate de leche.',
                'cost_usd' => 0.30,
                'price_usd' => 0.55,
                'stock' => 260,
                'min_stock' => 45,
                'unit' => 'und',
                'active' => true,
                'has_vat' => true,
            ],
        ];

        foreach ($products as $productData) {
            Product::query()->updateOrCreate(
                ['sku' => $productData['sku']],
                [
                    'category_id' => $categoryByName[$productData['category']] ?? null,
                    'name' => $productData['name'],
                    'description' => $productData['description'],
                    'cost_usd' => $productData['cost_usd'],
                    'price_usd' => $productData['price_usd'],
                    'stock' => $productData['stock'],
                    'min_stock' => $productData['min_stock'],
                    'unit' => $productData['unit'],
                    'active' => $productData['active'],
                    'has_vat' => $productData['has_vat'],
                ]
            );
        }

        $suppliers = [
            [
                'name' => 'Distribuidora Avila C.A.',
                'rif' => 'J-40123456-7',
                'phone' => '+58 212-5551010',
                'email' => 'ventas@distavila.com.ve',
                'address' => 'La Yaguara, Caracas, Distrito Capital',
                'active' => true,
            ],
            [
                'name' => 'Alimentos Centro Norte C.A.',
                'rif' => 'J-40987654-3',
                'phone' => '+58 241-5552211',
                'email' => 'pedidos@acn.com.ve',
                'address' => 'Zona Industrial Valencia, Carabobo',
                'active' => true,
            ],
            [
                'name' => 'Comercializadora Guayana, C.A.',
                'rif' => 'J-41234567-8',
                'phone' => '+58 286-5553344',
                'email' => 'contacto@comguayana.com',
                'address' => 'Unare, Puerto Ordaz, Bolivar',
                'active' => true,
            ],
            [
                'name' => 'Insumos Occidente 2000 C.A.',
                'rif' => 'J-39876543-2',
                'phone' => '+58 261-5557788',
                'email' => 'atencion@insoccidente.com',
                'address' => 'Zona Industrial San Francisco, Zulia',
                'active' => true,
            ],
            [
                'name' => 'Proveeduria Los Andes C.A.',
                'rif' => 'J-41555000-1',
                'phone' => '+58 274-5558899',
                'email' => 'compras@plandes.com.ve',
                'address' => 'Ejido, Merida, Merida',
                'active' => true,
            ],
        ];

        foreach ($suppliers as $supplierData) {
            Supplier::query()->updateOrCreate(
                ['rif' => $supplierData['rif']],
                [
                    'name' => $supplierData['name'],
                    'phone' => $supplierData['phone'],
                    'email' => $supplierData['email'],
                    'address' => $supplierData['address'],
                    'active' => $supplierData['active'],
                ]
            );
        }

        $customers = [
            [
                'name' => 'Carlos Mendoza',
                'document_type' => 'V',
                'document_number' => '18234567',
                'phone' => '+58 412-5567788',
                'email' => 'carlos.mendoza@gmail.com',
                'address' => 'El Paraiso, Caracas, Distrito Capital',
                'active' => true,
            ],
            [
                'name' => 'Maria Fernanda Rojas',
                'document_type' => 'V',
                'document_number' => '21456789',
                'phone' => '+58 414-6678899',
                'email' => 'maria.rojas@gmail.com',
                'address' => 'Naguanagua, Valencia, Carabobo',
                'active' => true,
            ],
            [
                'name' => 'Constructora El Saman C.A.',
                'document_type' => 'J',
                'document_number' => '409988776',
                'phone' => '+58 212-4489900',
                'email' => 'administracion@elsaman.com',
                'address' => 'Chacao, Caracas, Distrito Capital',
                'active' => true,
            ],
            [
                'name' => 'Panaderia San Benito C.A.',
                'document_type' => 'J',
                'document_number' => '406654321',
                'phone' => '+58 261-4455123',
                'email' => 'pedidos@sanbenito.com.ve',
                'address' => 'Maracaibo, Zulia',
                'active' => true,
            ],
            [
                'name' => 'Luisana Perez',
                'document_type' => 'V',
                'document_number' => '25678123',
                'phone' => '+58 424-9901122',
                'email' => 'luisanaperez@gmail.com',
                'address' => 'Lecheria, Anzoategui',
                'active' => true,
            ],
            [
                'name' => 'Servicios Integrales Oriente C.A.',
                'document_type' => 'J',
                'document_number' => '412220998',
                'phone' => '+58 281-6677011',
                'email' => 'facturacion@sioriente.com',
                'address' => 'Puerto La Cruz, Anzoategui',
                'active' => true,
            ],
            [
                'name' => 'Jose Gregorio Diaz',
                'document_type' => 'V',
                'document_number' => '17345009',
                'phone' => '+58 416-3355788',
                'email' => 'jgdiaz@gmail.com',
                'address' => 'Barinas, Barinas',
                'active' => true,
            ],
            [
                'name' => 'Clinica Los Proceres C.A.',
                'document_type' => 'J',
                'document_number' => '401118887',
                'phone' => '+58 212-9012244',
                'email' => 'compras@proceres.com.ve',
                'address' => 'San Cristobal, Tachira',
                'active' => true,
            ],
        ];

        foreach ($customers as $customerData) {
            Customer::query()->updateOrCreate(
                [
                    'document_type' => $customerData['document_type'],
                    'document_number' => $customerData['document_number'],
                ],
                [
                    'name' => $customerData['name'],
                    'phone' => $customerData['phone'],
                    'email' => $customerData['email'],
                    'address' => $customerData['address'],
                    'active' => $customerData['active'],
                ]
            );
        }
    }
}
