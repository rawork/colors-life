<?php

namespace Fuga\Component\Scheduler;

class Scheduler 
{
	
	private $tasks;
	
	public function __construct() {
		$this->tasks = array(
			'maillist' => array(
				'className' => 'Fuga\\CMSBundle\\Model\\MaillistManager',
				'methodName' => 'processMessage',
				'frequency' => 'minute',
				'params' => array()
			)
		);
	}
	
	public function registerTask($name, $className, $methodName, $frequency = 'hour', $params = array()) {
		$this->tasks[$name] = array(
			'className' => $className,
			'methodName' => $methodName,
			'frequency' => $frequency,
			'params' => $params
		);
	}
	
	public function unregisterTask($name) {
		unset($this->tasks[$name]);
	}
	
	public function processTasks($frequency) {
		set_time_limit(0);
		foreach ($this->tasks as $name => $params) {
			if ($params['frequency'] == $frequency) {
				$this->processTask($name);
			}
		}
	}
	
	public function processTask($name, $params = array()) {
		if (!isset($this->tasks[$name])) {
			throw new \ScheduleException('Задача "'.$name.'" не зарегистрирована в планировщике');
		}
		$className = $this->tasks[$name]['className'];
		$methodName = $this->tasks[$name]['methodName'];
		$params = $this->tasks[$name]['params'];
		$obj = new $className();
		$reflectionObj = new \ReflectionClass($className);
		$reflectionObj->getMethod($methodName)->invokeArgs($obj, $params);
	}

	public function everyMinute() {
		$this->processTasks('minute');
	}
	
	public function everyHour() {
		$this->processTasks('hour');
	}
	
	public function everyDay() {
		$this->processTasks('day');
	}
	
	public function everyWeek() {
		$this->processTasks('week');
	}
	
	public function everyMonth() {
		$this->processTasks('month');
	}
	
}

