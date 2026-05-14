-- Permite persistir validaciones diferidas por fase de aprendizaje.
ALTER TABLE ai.product_validation_results
    DROP CONSTRAINT IF EXISTS product_validation_results_decision_check;

ALTER TABLE ai.product_validation_results
    ADD CONSTRAINT product_validation_results_decision_check
    CHECK(decision IN ('approved', 'rejected', 'revision_humana', 'pending_validacion_ia', 'pending_review'));
