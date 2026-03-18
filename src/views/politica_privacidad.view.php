<?php 
$page_title = "Política de Privacidad y Tratamiento de Datos - VIVA";
$body_class = "flex flex-col min-h-screen font-sans text-oscuro bg-fondo-claro";
require_once __DIR__ . '/partials/base_head.php'; 
require_once __DIR__ . "/partials/header.php"; 
?>
<main class="flex-grow container mx-auto px-4 py-8 max-w-4xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 md:p-12">
        <h1 class="text-3xl font-bold text-tierra-oscuro mb-6">Política de Privacidad y Tratamiento de Datos Personales</h1>
        <div class="prose max-w-none text-gray-700">
            <p class="mb-8 text-sm text-gray-500"><i>Documento redactado en estricto cumplimiento de la Ley Estatutaria 1581 de 2012 y el Decreto Reglamentario 1377 de 2013 de la República de Colombia.</i></p>
            
            <h2 class="text-xl font-bold mt-8 mb-4 text-tierra-medio border-b pb-2">1. Identificación del Responsable del Tratamiento</h2>
            <ul class="list-none pl-0 mb-6 space-y-1">
                <li><strong>Razón Social / Nombre:</strong> Proyecto VIVA (Marketplace)</li>
                <li><strong>Domicilio Principal:</strong> Bucaramanga, Santander, Colombia</li>
                <li><strong>Correo Electrónico:</strong> privacidad@viva-artesanias.com.co</li>
            </ul>

            <h2 class="text-xl font-bold mt-8 mb-4 text-tierra-medio border-b pb-2">2. Objetivo y Alcance</h2>
            <p class="mb-4">La presente política establece los lineamientos estrictos para la recolección, almacenamiento, uso, circulación, supresión y protección de los datos personales de artesanos, productores, clientes nacionales y compradores internacionales que interactúan con la plataforma VIVA.</p>

            <h2 class="text-xl font-bold mt-8 mb-4 text-tierra-medio border-b pb-2">3. Finalidad del Tratamiento de los Datos</h2>
            <p class="mb-4">Los datos personales proporcionados serán utilizados exclusivamente para las siguientes finalidades:</p>
            <ul class="list-disc pl-5 mb-6 space-y-2">
                <li><strong>Gestión de Productores:</strong> Verificación de identidad y validación del origen de las artesanías indígenas para garantizar la autenticidad del producto en el exterior.</li>
                <li><strong>Logística y Exportación:</strong> Compartir información estrictamente necesaria con empresas de transporte, aduanas y mensajería internacional para la entrega de los productos.</li>
                <li><strong>Transacciones Financieras:</strong> Procesamiento de pagos, liquidación de ventas para los artesanos y prevención de fraudes a través de pasarelas de pago aliadas.</li>
                <li><strong>Comunicaciones:</strong> Envío de notificaciones transaccionales (estado de pedidos) y, previo consentimiento, información sobre nuevas colecciones o campañas comerciales.</li>
            </ul>

            <h2 class="text-xl font-bold mt-8 mb-4 text-tierra-medio border-b pb-2">4. Tratamiento de Datos Sensibles</h2>
            <p class="mb-4">VIVA reconoce que la información relativa a la pertenencia a comunidades indígenas puede considerarse un dato sensible. Su tratamiento es estrictamente voluntario y se utilizará de manera exclusiva para la catalogación, certificación de origen artesanal y promoción cultural dentro del marketplace. Ningún usuario está obligado a suministrar datos sensibles.</p>

            <h2 class="text-xl font-bold mt-8 mb-4 text-tierra-medio border-b pb-2">5. Derechos de los Titulares (Derechos ARCO)</h2>
            <p class="mb-4">Como titular de sus datos personales, usted tiene derecho a:</p>
            <ul class="list-disc pl-5 mb-6 space-y-2">
                <li><strong>Conocer, actualizar y rectificar</strong> sus datos personales en cualquier momento.</li>
                <li><strong>Solicitar prueba</strong> de la autorización otorgada a VIVA.</li>
                <li><strong>Ser informado</strong> sobre el uso que se le ha dado a sus datos.</li>
                <li><strong>Revocar la autorización</strong> y/o solicitar la supresión del dato cuando no se respeten los principios, derechos y garantías constitucionales y legales.</li>
                <li><strong>Acceder en forma gratuita</strong> a sus datos personales que hayan sido objeto de Tratamiento.</li>
            </ul>

            <h2 class="text-xl font-bold mt-8 mb-4 text-tierra-medio border-b pb-2">6. Procedimiento para Consultas y Reclamos</h2>
            <p class="mb-4">Para ejercer sus derechos, el titular debe enviar una solicitud formal al correo <strong>privacidad@viva-artesanias.com.co</strong>. Las consultas serán atendidas en un término máximo de diez (10) días hábiles. Los reclamos (como solicitudes de eliminación) se atenderán en un máximo de quince (15) días hábiles, de acuerdo con la ley colombiana.</p>

            <h2 class="text-xl font-bold mt-8 mb-4 text-tierra-medio border-b pb-2">7. Seguridad de la Información</h2>
            <p class="mb-6">VIVA implementa medidas técnicas, humanas y administrativas, incluyendo el almacenamiento seguro en bases de datos cifradas y conexiones HTTPS, para otorgar seguridad a los registros evitando su adulteración, pérdida, consulta, uso o acceso no autorizado o fraudulento.</p>
        </div>
    </div>
</main>
<?php include_once ROOT_PATH . "src/views/partials/footer.php"; ?>
</body>
</html>