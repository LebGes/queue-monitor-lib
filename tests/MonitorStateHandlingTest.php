<?php

namespace Novikovvs\QueueMonitor\Tests;

use Novikovvs\QueueMonitor\Models\Monitor;
use Novikovvs\QueueMonitor\Tests\Support\IntentionallyFailedException;
use Novikovvs\QueueMonitor\Tests\Support\MonitoredFailingJob;
use Novikovvs\QueueMonitor\Tests\Support\MonitoredFailingJobWithHugeExceptionMessage;

class MonitorStateHandlingTest extends TestCase
{
    public function testFailing()
    {
        $this->dispatch(new MonitoredFailingJob());
        $this->workQueue();

        $this->assertCount(1, Monitor::all());
        $this->assertInstanceOf(Monitor::class, $monitor = Monitor::query()->first());
        $this->assertEquals(MonitoredFailingJob::class, $monitor->name);
        $this->assertEquals(IntentionallyFailedException::class, $monitor->exception_class);
        $this->assertEquals('Whoops', $monitor->exception_message);
        $this->assertInstanceOf(IntentionallyFailedException::class, $monitor->getException());
    }

    public function testFailingWithHugeExceptionMessage()
    {
        $this->dispatch(new MonitoredFailingJobWithHugeExceptionMessage());
        $this->workQueue();

        $this->assertCount(1, Monitor::all());
        $this->assertInstanceOf(Monitor::class, $monitor = Monitor::query()->first());
        $this->assertEquals(MonitoredFailingJobWithHugeExceptionMessage::class, $monitor->name);
        $this->assertEquals(IntentionallyFailedException::class, $monitor->exception_class);
        $this->assertEquals(str_repeat('x', config('queue-monitor.db_max_length_exception_message')), $monitor->exception_message);
        $this->assertInstanceOf(IntentionallyFailedException::class, $monitor->getException());
    }
}
