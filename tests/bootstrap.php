<?php

$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
}

require_once __DIR__ . '/../src/functions/database.php';
require_once __DIR__ . '/../src/exceptions/AIProviderException.php';
require_once __DIR__ . '/../src/services/HashService.php';
require_once __DIR__ . '/../src/services/AIProviderRouter.php';
require_once __DIR__ . '/../src/services/ImageSignatureService.php';
require_once __DIR__ . '/../src/services/TextEmbeddingService.php';
require_once __DIR__ . '/../src/services/ProductValidationService.php';
require_once __DIR__ . '/../src/workers/Jobs/ProductValidationJob.php';
require_once __DIR__ . '/../src/workers/Worker.php';
require_once __DIR__ . '/../src/workers/CartWorker.php';
require_once __DIR__ . '/../src/workers/ValidationWorker.php';
