<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CursoControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        // Crea un cliente para simular una petición HTTP
        $client = static::createClient();

        // Realiza una petición a la URL /curso
        $client->request('GET', '/curso');

        // Verifica que la respuesta es correcta (código 200)
        $this->assertResponseIsSuccessful();

        // Verifica que el contenido contiene el texto esperado
        $this->assertSelectorTextContains('body', 'Página de Cursos');
    }
}