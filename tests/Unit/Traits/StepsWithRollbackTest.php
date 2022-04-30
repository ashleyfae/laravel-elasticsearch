<?php
/**
 * StepsWithRollbackTest.php
 *
 * @package   laravel-elasticsearch
 * @copyright Copyright (c) 2022, Ashley Gibson
 * @license   MIT
 */

namespace Ashleyfae\LaravelElasticsearch\Tests\Unit\Traits;

use Ashleyfae\LaravelElasticsearch\Services\IndexMigration\Contracts\ReversibleStep;
use Ashleyfae\LaravelElasticsearch\Services\IndexMigration\Contracts\Step;
use Ashleyfae\LaravelElasticsearch\Tests\Helpers\CanTestInaccessibleMethods;
use Ashleyfae\LaravelElasticsearch\Traits\StepsWithRollback;
use Mockery;
use PHPUnit\Framework\TestCase;

class StepsWithRollbackTest extends TestCase
{
    use CanTestInaccessibleMethods;

    /**
     * @return StepsWithRollback|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getTraitMock()
    {
        return $this->getMockForTrait(StepsWithRollback::class);
    }

    /**
     * @covers StepsWithRollback::addCompletedStep()
     */
    public function testCanAddStep()
    {
        $mock = $this->getTraitMock();

        $this->assertEmpty($this->getInaccessibleProperty($mock, 'completedSteps'));

        $step = Mockery::mock(Step::class);

        $this->invokeInaccessibleMethod($mock, 'addCompletedStep', $step);

        $this->assertSame([$step], $this->getInaccessibleProperty($mock, 'completedSteps'));
    }

    /**
     * @covers StepsWithRollback::rollbackSteps()
     */
    public function testCanRollbackSteps()
    {
        $mock           = $this->getTraitMock();
        $step           = Mockery::mock(Step::class);
        $reversibleStep = Mockery::mock(ReversibleStep::class);
        $reversibleStep->expects('down')->once();

        $this->invokeInaccessibleMethod($mock, 'addCompletedStep', $step);
        $this->invokeInaccessibleMethod($mock, 'addCompletedStep', $reversibleStep);

        $this->invokeInaccessibleMethod($mock, 'rollbackSteps');

        $this->expectNotToPerformAssertions();
    }
}
