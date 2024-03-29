<?php

namespace App\DataFixtures;

use App\Entity\SendList;
use App\Entity\Service;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CheckinFixtures extends Fixture implements DependentFixtureInterface
{
    private $params;
    /**
     * @var CommonGroundService
     */
    private $commonGroundService;

    public function __construct(ParameterBagInterface $params, CommonGroundService $commonGroundService)
    {
        $this->params = $params;
        $this->commonGroundService = $commonGroundService;
    }

    public function getDependencies()
    {
        return [
            ZuiddrechtFixtures::class,
        ];
    }

    public function load(ObjectManager $manager)
    {
        if (
            !$this->params->get('app_build_all_fixtures') &&
            $this->params->get('app_domain') != 'checking.nu' && strpos($this->params->get('app_domain'), 'checking.nu') == false &&
            $this->params->get('app_domain') != 'zuiddrecht.nl' && strpos($this->params->get('app_domain'), 'zuiddrecht.nl') == false &&
            $this->params->get('app_domain') != 'zuid-drecht.nl' && strpos($this->params->get('app_domain'), 'zuid-drecht.nl') == false
        ) {
            return false;
        }

        $id = Uuid::fromString('eb7ffa01-4803-44ce-91dc-d4e3da7917da');
        $service = new Service();
        $service->setType('mailer');
        $service->setOrganization($this->commonGroundService->cleanUrl(['component'=>'wrc', 'type'=>'organizations', 'id'=>'c571bdad-f34c-4e24-94e7-74629cfaccc9']));
        $service->setAuthorization('mailgun+api://!changeme!:mail.checking.nu@api.eu.mailgun.net');
        $manager->persist($service);
        $service->setId($id);
        $manager->persist($service);

        $manager->flush();

        $id = Uuid::fromString('98e72bec-e632-4b61-ba0f-53452ffe5ed9');
        $newsLetterList = new SendList();
        $newsLetterList->setName('Newsletter');
        $newsLetterList->setDescription('Newsletter for Checkin');
        $newsLetterList->setMail(true);
        $newsLetterList->setOrganization($this->commonGroundService->cleanUrl(['component'=>'wrc', 'type'=>'organizations', 'id'=>'c571bdad-f34c-4e24-94e7-74629cfaccc9']));
        $manager->persist($newsLetterList);
        $newsLetterList->setId($id);
        $manager->persist($newsLetterList);

        $manager->flush();
    }
}
