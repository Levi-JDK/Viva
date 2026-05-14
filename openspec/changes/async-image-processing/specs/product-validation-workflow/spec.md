# Delta for product-validation-workflow

## Purpose

Worker-driven product validation pipeline. Modified to process images before AI validation and transition `pending_images → pending_review`.

## MODIFIED Requirements

### Requirement: Status Transition pending_images to pending_review

The worker MUST transition products from `pending_images` to `pending_review` only after successful image processing.

#### Scenario: Images processed before AI validation

- GIVEN a product with `validation_status = 'pending_images'`
- WHEN the worker successfully moves the temp file, generates variants, and updates `tab_imagenes`
- THEN `validation_status` is updated to `'pending_review'` and AI validation proceeds

### Requirement: Check Constraint Allows pending_images

The `chk_pp_stock_activo` constraint MUST allow `validation_status = 'pending_images'` with `is_active = false`.

(Previously: constraint only allowed `(pending_review, false)`, `(stock > 0, true)`, or `(stock = 0, false)`.)

#### Scenario: New product with pending_images and stock > 0

- GIVEN a product insert with `validation_status = 'pending_images'` and `is_active = false` and `stock > 0`
- WHEN the row is inserted
- THEN the constraint passes without error

### Requirement: Worker Error Handling for Missing Temp File

#### Scenario: Invalid image file in worker → DLQ

- GIVEN a product with `pending_images` status and a temp file that is corrupt or unreadable
- WHEN the worker fails to generate variants
- THEN the product stays `pending_images`, the job fails, and after retries exhausts, enters the Dead Letter Queue
- AND no partial variants remain on disk

#### Scenario: Temp file missing before worker reads it

- GIVEN a product with `pending_images` status and a temp path pointing to a non-existent file
- WHEN the worker attempts to process images
- THEN the worker logs the error and the product remains in `pending_images`
- AND the job is sent to the DLQ after exhausting retries

### Requirement: Existing Products Unaffected

Products created before this change MUST have their existing behavior preserved.

#### Scenario: Existing approved product unchanged

- GIVEN an existing product with `validation_status = 'approved'` and images already in `tab_imagenes`
- WHEN the system operates normally
- THEN the product remains visible and functional with no change to its data or paths