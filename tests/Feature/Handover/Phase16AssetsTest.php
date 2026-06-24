<?php

namespace Tests\Feature\Handover;

use App\Console\Commands\GenerateUatReportCommand;
use App\Console\Commands\HandoverCheckCommand;
use Tests\TestCase;

class Phase16AssetsTest extends TestCase
{
    public function test_phase_16_commands_exist(): void
    {
        $this->assertTrue(class_exists(HandoverCheckCommand::class));
        $this->assertTrue(class_exists(GenerateUatReportCommand::class));
    }

    public function test_phase_15_and_16_documentation_files_exist(): void
    {
        $this->assertFileExists(base_path('docs/PHASE_15_DOCKER_OPTIONAL.md'));
        $this->assertFileExists(base_path('docs/PHASE_16_QA_HANDOVER.md'));
        $this->assertFileExists(base_path('qa/UAT_TEST_PLAN.md'));
        $this->assertFileExists(base_path('qa/HANDOVER_CHECKLIST.md'));
    }
}
