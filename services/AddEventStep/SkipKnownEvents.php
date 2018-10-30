<?php declare(strict_types=1);

namespace AddEventStep;

use Event;
use EventFactory;
use Improved\IteratorPipeline\Pipeline;
use Jasny\ValidationResult;

/**
 * Some (but not all) of the new events may already known and processed. Submitting events is idempotent. We skip
 * through these events.
 *
 * If a fork is detected we set an error. This validation object is shared with all steps. Next steps in the pipeline
 * should check the validation and not perform their action. All skipped events are collected at the end to create an
 * error event.
 */
class SkipKnownEvents
{
    /**
     * Invoke the step.
     *
     * @param Pipeline         $pipeline
     * @param ValidationResult $validation
     * @return Pipeline
     */
    public function __invoke(Pipeline $pipeline, ValidationResult $validation): Pipeline
    {
        $forked = false;

        return $pipeline->filter(function(Event $new, Event $known) use ($validation, &$forked): bool {
            if (isset($know) && $known->hash !== $new->hash) {
                $validation->addError("fork detected; conflict on '%s' and '%s'", $new->hash, $known->hash);
                $forked = true;
            }

            return !isset($know) || $forked;
        });
    }
}
