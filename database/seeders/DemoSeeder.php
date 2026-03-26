<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Comercio;
use App\Models\Articulo;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Categorías
        $categorias = [
            ['nombre' => 'Indumentaria', 'slug' => 'indumentaria', 'icono' => '👗'],
            ['nombre' => 'Electrónica', 'slug' => 'electronica', 'icono' => '📱'],
            ['nombre' => 'Hogar', 'slug' => 'hogar', 'icono' => '🏠'],
            ['nombre' => 'Alimentos', 'slug' => 'alimentos', 'icono' => '🍕'],
            ['nombre' => 'Deportes', 'slug' => 'deportes', 'icono' => '⚽'],
            ['nombre' => 'Belleza', 'slug' => 'belleza', 'icono' => '💄'],
            ['nombre' => 'Librería', 'slug' => 'libreria', 'icono' => '📚'],
            ['nombre' => 'Automotor', 'slug' => 'automotor', 'icono' => '🚗'],
        ];

        foreach ($categorias as $cat) {
            Categoria::create($cat);
        }

        // Comercio 1
        $c1 = Comercio::create([
            'nombre' => 'Moda Sur',
            'slug' => 'moda-sur',
            'descripcion' => 'Tu tienda de moda urbana en el barrio. Ropa, accesorios y calzado al mejor precio.',
            'logo_url' => null,
            'whatsapp' => '5491155001234',
            'zona_barrio' => 'Palermo',
            'direccion' => 'Av. Santa Fe 3200',
            'categoria_comercio' => 'Indumentaria',
            'email' => 'modasur@demo.com',
            'password' => 'password123',
        ]);

        // Comercio 2
        $c2 = Comercio::create([
            'nombre' => 'TechZone',
            'slug' => 'techzone',
            'descripcion' => 'Tecnología, gadgets y accesorios. Servicio técnico y asesoramiento personalizado.',
            'logo_url' => null,
            'whatsapp' => '5491155005678',
            'zona_barrio' => 'Microcentro',
            'direccion' => 'Av. Corrientes 1500',
            'categoria_comercio' => 'Electrónica',
            'email' => 'techzone@demo.com',
            'password' => 'password123',
        ]);

        // Comercio 3
        $c3 = Comercio::create([
            'nombre' => 'Casa Deco',
            'slug' => 'casa-deco',
            'descripcion' => 'Decoración, bazar y artículos para tu hogar. Envíos a toda la zona.',
            'logo_url' => null,
            'whatsapp' => '5491155009012',
            'zona_barrio' => 'Belgrano',
            'direccion' => 'Cabildo 2800',
            'categoria_comercio' => 'Hogar',
            'email' => 'casadeco@demo.com',
            'password' => 'password123',
        ]);

        // Artículos Moda Sur
        $articulosModaSur = [
            ['nombre_producto' => 'Remera Oversize Algodón', 'descripcion_articulo' => 'Remera oversize 100% algodón peinado. Disponible en negro, blanco y beige. Talles S a XL.', 'precio_ars' => 18500.00, 'categoria' => 'Indumentaria', 'imagen_url' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=400'],
            ['nombre_producto' => 'Jean Mom Fit', 'descripcion_articulo' => 'Jean mom fit tiro alto con roturas. Denim premium, calce perfecto. Talles 24 al 32.', 'precio_ars' => 42000.00, 'categoria' => 'Indumentaria', 'imagen_url' => 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=400'],
            ['nombre_producto' => 'Zapatillas Urbanas Blancas', 'descripcion_articulo' => 'Zapatillas urbanas blancas con plataforma liviana. Ideales para uso diario. Talles 35 al 44.', 'precio_ars' => 55000.00, 'categoria' => 'Indumentaria', 'imagen_url' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=400'],
            ['nombre_producto' => 'Campera Puffer Invierno', 'descripcion_articulo' => 'Campera puffer ultra liviana con relleno térmico. Capucha desmontable. Colores: negro, verde militar, bordo.', 'precio_ars' => 89000.00, 'categoria' => 'Indumentaria', 'imagen_url' => 'https://images.unsplash.com/photo-1544923246-77307dd270b5?w=400'],
            ['nombre_producto' => 'Gorra Dad Cap', 'descripcion_articulo' => 'Gorra dad cap con bordado frontal. Ajuste con hebilla trasera. Varios colores disponibles.', 'precio_ars' => 12000.00, 'categoria' => 'Indumentaria', 'imagen_url' => 'https://images.unsplash.com/photo-1588850561407-ed78c334e67a?w=400'],
        ];

        foreach ($articulosModaSur as $art) {
            $c1->articulos()->create($art);
        }

        // Artículos TechZone
        $articulosTech = [
            ['nombre_producto' => 'Auriculares Bluetooth TWS', 'descripcion_articulo' => 'Auriculares inalámbricos con cancelación de ruido activa. 30hs de batería total. Estuche de carga incluido.', 'precio_ars' => 35000.00, 'categoria' => 'Electrónica', 'imagen_url' => 'https://images.unsplash.com/photo-1590658268037-6bf12f032f55?w=400'],
            ['nombre_producto' => 'Cargador Inalámbrico 15W', 'descripcion_articulo' => 'Base de carga inalámbrica Qi 15W. Compatible con iPhone y Samsung. LED indicador de carga.', 'precio_ars' => 22000.00, 'categoria' => 'Electrónica', 'imagen_url' => 'https://images.unsplash.com/photo-1591290619370-f0cd0e3a3251?w=400'],
            ['nombre_producto' => 'Teclado Mecánico RGB', 'descripcion_articulo' => 'Teclado mecánico gaming con switches blue. Retroiluminación RGB programable. Layout español.', 'precio_ars' => 48000.00, 'categoria' => 'Electrónica', 'imagen_url' => 'https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?w=400'],
            ['nombre_producto' => 'Smartwatch Deportivo', 'descripcion_articulo' => 'Reloj inteligente con GPS, monitor cardíaco y SpO2. Resistente al agua IP68. Batería 7 días.', 'precio_ars' => 67000.00, 'categoria' => 'Electrónica', 'imagen_url' => 'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?w=400'],
            ['nombre_producto' => 'Parlante Portátil 20W', 'descripcion_articulo' => 'Parlante Bluetooth portátil 20W. Resistente al agua IPX5. 12hs de batería. Función TWS para estéreo.', 'precio_ars' => 38000.00, 'categoria' => 'Electrónica', 'imagen_url' => 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=400'],
        ];

        foreach ($articulosTech as $art) {
            $c2->articulos()->create($art);
        }

        // Artículos Casa Deco
        $articulosDeco = [
            ['nombre_producto' => 'Lámpara Colgante Industrial', 'descripcion_articulo' => 'Lámpara colgante estilo industrial con pantalla metálica negra. Cable regulable. Foco E27 no incluido.', 'precio_ars' => 32000.00, 'categoria' => 'Hogar', 'imagen_url' => 'https://images.unsplash.com/photo-1507473885765-e6ed057ab6fe?w=400'],
            ['nombre_producto' => 'Set Tazas Cerámica x4', 'descripcion_articulo' => 'Set de 4 tazas de cerámica artesanal. Capacidad 350ml. Colores tierra: arena, arcilla, musgo, carbón.', 'precio_ars' => 26000.00, 'categoria' => 'Hogar', 'imagen_url' => 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=400'],
            ['nombre_producto' => 'Espejo Redondo Marco Madera', 'descripcion_articulo' => 'Espejo circular con marco de madera natural. Diámetro 60cm. Incluye sistema de colgar.', 'precio_ars' => 45000.00, 'categoria' => 'Hogar', 'imagen_url' => 'https://images.unsplash.com/photo-1618220179428-22790b461013?w=400'],
            ['nombre_producto' => 'Vela Aromática Soja 250g', 'descripcion_articulo' => 'Vela de cera de soja natural con mecha de algodón. Aromas: vainilla, lavanda, canela. Duración 40hs.', 'precio_ars' => 15000.00, 'categoria' => 'Hogar', 'imagen_url' => 'https://images.unsplash.com/photo-1602607714066-c5d78e60dc8d?w=400'],
            ['nombre_producto' => 'Organizador Escritorio Bambú', 'descripcion_articulo' => 'Organizador de escritorio en bambú con 5 compartimentos. Ideal para home office. Medidas: 25x15x12cm.', 'precio_ars' => 19500.00, 'categoria' => 'Hogar', 'imagen_url' => 'https://images.unsplash.com/photo-1544457070-4cd773b4d71e?w=400'],
        ];

        foreach ($articulosDeco as $art) {
            $c3->articulos()->create($art);
        }
    }
}
