<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Customer;
use Illuminate\Database\Seeder;

class LibraryDataSeeder extends Seeder
{
    public function run(): void
    {
        $literature = Category::firstOrCreate(['name' => 'Literatura'], ['description' => 'Novela, cuento y poesía.']);
        $education = Category::firstOrCreate(['name' => 'Educación'], ['description' => 'Textos de apoyo y aprendizaje.']);
        $stationery = Category::firstOrCreate(['name' => 'Papelería'], ['description' => 'Material para estudio y oficina.']);

        $marquez = Author::firstOrCreate(['name' => 'Gabriel García Márquez'], ['biography' => 'Escritor colombiano y ganador del Premio Nobel.']);
        $rowling = Author::firstOrCreate(['name' => 'J. K. Rowling'], ['biography' => 'Autora británica de literatura juvenil.']);

        Book::updateOrCreate(['isbn' => '9780307474728'], ['title' => 'Cien años de soledad', 'author_id' => $marquez->id, 'category_id' => $literature->id, 'price' => 420.00, 'stock' => 12, 'description' => 'Novela clásica latinoamericana.', 'active' => true]);
        Book::updateOrCreate(['isbn' => '9788478884452'], ['title' => 'Harry Potter y la piedra filosofal', 'author_id' => $rowling->id, 'category_id' => $literature->id, 'price' => 395.00, 'stock' => 9, 'description' => 'Primera novela de la saga.', 'active' => true]);
        Book::updateOrCreate(['isbn' => 'PAP-001'], ['title' => 'Cuaderno universitario', 'author_id' => null, 'category_id' => $stationery->id, 'price' => 55.00, 'stock' => 40, 'description' => 'Cuaderno de 100 hojas rayadas.', 'active' => true]);
        Book::updateOrCreate(['isbn' => 'EDU-001'], ['title' => 'Matemática básica', 'author_id' => null, 'category_id' => $education->id, 'price' => 185.00, 'stock' => 15, 'description' => 'Material de práctica escolar.', 'active' => true]);

        Customer::firstOrCreate(['email' => 'cliente@libreria.test'], ['name' => 'Cliente de prueba', 'phone' => '9999-0000', 'address' => 'Tegucigalpa']);
    }
}
