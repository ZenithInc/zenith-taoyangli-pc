<?php

declare(strict_types=1);

// PC has no active queue consumer or scheduler workload. Keeping this empty is
// intentional: an HTTP Pod must never start generic background processes.
return [];
