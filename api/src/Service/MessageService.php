<?php
// src/Service/HuwelijkService.php
namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface as CacheInterface;

Use App\Entity\Message;
Use App\Service\CommonGroundService;

class MessageService
{
	private $params;
	private $cache;
	private $commonGroundService;
	
	public function __construct(ParameterBagInterface $params, CacheInterface $cache,  CommonGroundService $commonGroundService)
	{
		$this->params = $params;
		$this->cash = $cache;
		$this->commonGroundService = $commonGroundService;		
	}
	
	public function sendMessage(Message $message)
	{
		$sender = $this->commonGroundService->getResource($message->getSender());
		$reciever = $this->commonGroundService->getResource($message->getReciever());
		$content = $this->commonGroundService->getResource($message->getContent().'/render');
		$html = $content['content'];
		$text = strip_tags(preg_replace('#<br\s*/?>#i', "\n", $html), '\n');		
		
		$messageBird = new \MessageBird\Client($message->getService()->getAuthorization());
				
		$sms             = new \MessageBird\Objects\Message();
		$sms->originator = $sender['telephones'][0]['telephone'];
		$sms->recipients = array($reciever['telephones'][0]['telephone']);
		$sms->body       = 'test 2';//$text;
		
		
		try {
			$MessageResult = $messageBird->messages->create($sms);
		} catch (\MessageBird\Exceptions\AuthenticateException $e) {
			// That means that your accessKey is unknown
			echo 'wrong login';
			
		} catch (\MessageBird\Exceptions\BalanceException $e) {
			// That means that you are out of credits, so do something about it.
			echo 'no balance';
			
		} catch (\Exception $e) {
			echo $e->getMessage();
		}
		
		$message->setSend(New \Datetime);
		$message->setStatus('send');
		$message->setServiceId($MessageResult->id);
		//
		return $message;
		
		
	}
	
	
}
