<?php 
$page_title = "Términos y Condiciones - VIVA";
$body_class = "flex flex-col min-h-screen font-sans text-oscuro bg-fondo-claro";
require_once __DIR__ . '/partials/base_head.php'; 
require_once __DIR__ . "/partials/header.php"; 
?>
<main class="flex-grow container mx-auto px-4 py-8 max-w-4xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 md:p-12">
        <h1 class="text-3xl font-bold text-tierra-oscuro mb-6">Términos y Condiciones de Uso</h1>
        <div class="prose max-w-none text-gray-700">
            <p class="mb-8 text-sm text-gray-500"><i>Última actualización: <?= date('d/m/Y') ?></i></p>
            
            <h2 class="text-xl font-bold mt-8 mb-4 text-tierra-medio border-b pb-2">1. Naturaleza del Servicio</h2>
            <p class="mb-4">VIVA opera como una plataforma tecnológica (Marketplace) que facilita la exhibición, comercialización y exportación de artesanías indígenas colombianas. VIVA actúa como un intermediario entre los artesanos (productores) y los compradores a nivel nacional e internacional, gestionando la plataforma visual y tecnológica, pero no es el fabricante directo de los bienes ofrecidos.</p>

            <h2 class="text-xl font-bold mt-8 mb-4 text-tierra-medio border-b pb-2">2. Registro y Cuentas de Usuario</h2>
            <ul class="list-disc pl-5 mb-6 space-y-2">
                <li>Todo usuario, sea productor o cliente, debe proporcionar información veraz, exacta y actualizada durante el registro.</li>
                <li>La cuenta es personal e intransferible. El usuario es el único responsable de mantener la confidencialidad de sus credenciales de acceso.</li>
                <li>VIVA se reserva el derecho de suspender o cancelar cuentas que incumplan estas políticas o presenten comportamientos fraudulentos.</li>
            </ul>

            <h2 class="text-xl font-bold mt-8 mb-4 text-tierra-medio border-b pb-2">3. Obligaciones de los Productores (Artesanos)</h2>
            <p class="mb-4">Al registrarse como productor en VIVA, el usuario se compromete a:</p>
            <ul class="list-disc pl-5 mb-6 space-y-2">
                <li>Garantizar que los productos publicados son auténticos, elaborados a mano y respetan las tradiciones culturales descritas.</li>
                <li>Mantener el inventario actualizado y cumplir estrictamente con los tiempos de preparación y despacho pactados en la plataforma.</li>
                <li>Proporcionar embalajes adecuados que aseguren la integridad de las piezas durante trayectos internacionales.</li>
            </ul>

            <h2 class="text-xl font-bold mt-8 mb-4 text-tierra-medio border-b pb-2">4. Compras, Pagos y Exportación</h2>
            <ul class="list-disc pl-5 mb-6 space-y-2">
                <li><strong>Precios:</strong> Los precios publicados incluyen los costos de intermediación, pero pueden estar sujetos a costos adicionales de envío internacional y tasas de cambio.</li>
                <li><strong>Aduanas y Aranceles:</strong> Para compras internacionales, el comprador asume la responsabilidad de cualquier impuesto, arancel o retención aduanera exigida por su país de residencia.</li>
                <li><strong>Pagos:</strong> Todas las transacciones se realizan mediante pasarelas de pago seguras y certificadas. VIVA retendrá los fondos hasta la confirmación del despacho para proteger tanto al artesano como al comprador.</li>
            </ul>

            <h2 class="text-xl font-bold mt-8 mb-4 text-tierra-medio border-b pb-2">5. Propiedad Intelectual y Cultural</h2>
            <p class="mb-4">Los diseños, patrones y técnicas de las artesanías publicadas en VIVA son propiedad intelectual y cultural de las respectivas comunidades indígenas y creadores. Queda terminantemente prohibida la reproducción, copia o uso comercial de las fotografías y diseños del marketplace sin autorización previa y por escrito.</p>

            <h2 class="text-xl font-bold mt-8 mb-4 text-tierra-medio border-b pb-2">6. Derecho de Retracto y Devoluciones</h2>
            <p class="mb-4">De conformidad con el artículo 47 de la Ley 1480 de 2011 (Estatuto del Consumidor de Colombia):</p>
            <ul class="list-disc pl-5 mb-6 space-y-2">
                <li>El comprador tiene derecho a retractarse de su compra dentro de los cinco (5) días hábiles siguientes a la entrega del producto.</li>
                <li><strong>Excepciones:</strong> Por su naturaleza, no se aceptarán devoluciones de productos elaborados a medida, personalizados o que por razones de higiene no puedan ser retornados.</li>
                <li>Los costos de transporte para la devolución de piezas internacionales correrán por cuenta del comprador, a menos que el producto presente defectos de fábrica o no corresponda a lo solicitado.</li>
            </ul>

            <h2 class="text-xl font-bold mt-8 mb-4 text-tierra-medio border-b pb-2">7. Ley Aplicable y Jurisdicción</h2>
            <p class="mb-4">Estos Términos y Condiciones se rigen por las leyes de la República de Colombia. Cualquier controversia derivada del uso de la plataforma será sometida a los jueces y tribunales competentes en la ciudad de Bucaramanga, Santander.</p>
        </div>
    </div>
</main>
<?php include_once ROOT_PATH . "src/views/partials/footer.php"; ?>
</body>
</html>