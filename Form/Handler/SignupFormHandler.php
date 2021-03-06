<?php

namespace CCETC\DirectoryBundle\Form\Handler;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;

class SignupFormHandler
{
    protected $request;
    protected $form;
    protected $container;
    protected $registrationSetting;

    public function __construct($form, Request $request, $container)
    {
        $this->form = $form;
        $this->request = $request;
        $this->container = $container;
        $this->registrationSetting = $this->container->getParameter('ccetc_directory.registration_setting');
    }

    public function process()
    {
        // NOTE: for some reason, on some calls of this, registrationSetting has not been set
        $this->registrationSetting = $this->container->getParameter('ccetc_directory.registration_setting');

        if('POST' === $this->request->getMethod()) {
            $this->form->bindRequest($this->request);

            if($this->validateUser() && $this->form->isValid()) {
                $this->onSuccess();
                return true;
            } else {
                $this->form->addError(new FormError('Please correct the errors below and re-submit'));
            }

        }
        return false;
    }

    protected function validateUser()
    {
        // NOTE: for some reason, on some calls of this, registrationSetting has not been set
        $this->registrationSetting = $this->container->getParameter('ccetc_directory.registration_setting');
        if($this->registrationSetting == "none") return true;

        $listing = $this->form->getData();
        $userManager = $this->container->get('fos_user.user_manager');
        $valid = true;
        $password1 = $this->form->get('password1')->getData();
        $password2 = $this->form->get('password2')->getData();

        if($userManager->findUserByEmail($listing->getPrimaryEmail())) {
            $this->form->get('primaryEmail')->addError(new FormError('This e-mail address is already used'));
            $valid = false;
        }
        if($password1 != $password2) {
            $this->form->get('password1')->addError(new FormError('Passwords do not match'));
            $this->form->get('password2')->addError(new FormError('Passwords do not match'));
            $valid = false;                
        }    
        if($this->registrationSetting == "required" && (!isset($password1) || $password1 == "")) {
            $this->form->get('password1')->addError(new FormError('Please enter a password'));
            $valid = false;                                
        }

        return $valid;
    }

    protected function onSuccess()
    {
        $listing = $this->form->getData();
        $userManager = $this->container->get('fos_user.user_manager');
        $listingTypeHelper = $this->container->get('ccetc.directory.helper.listingtypehelper');
        $listingType = $listingTypeHelper->findOneByEntityClassPath("\\".get_class($listing));
        $listingAdmin = $listingType->getAdminClass();

        $listing->setStatus('new');

        if($this->registrationSetting != "none" && trim($this->form->get('password1')->getData()) != ""  ) {
            $userManager = $this->container->get('fos_user.user_manager');
            $user = $this->processUser($listing->getPrimaryEmail());
            $listing->setUser($user);
        }

        if($listing->getPhotoFile() && $this->form->get('photoFile')->getData()) {
            $listingAdmin->saveFile($listing);
        }

        $listingAdmin->create($listing);

        if($this->registrationSetting != "none" && isset($user)) {
            $user->setListing($listing);
            $userManager->updateUser($user);        
        }

        $this->sendSignupNotificationEmail($listing, $this->container->getParameter('ccetc_directory.admin_email'), $this->getPageLink().$listingAdmin->generateObjectUrl('edit', $listing));
        if($listing->getPrimaryEmail()) $this->sendWelcomeEmail($listing);
    }
    
    protected function processUser($email)
    {
        $userManager = $this->container->get('fos_user.user_manager');
        $user = $userManager->createUser();
        $user->setEmail($email);
        $user->setUsername($email);
        $user->setEnabled(true);
        $plainPassword = $this->form->get('password1')->getData();
        $user->setPlainPassword($plainPassword);
        $userManager->updateUser($user);

        $this->container->get('fos_user.security.login_manager')->loginUser(
                $this->container->getParameter('fos_user.firewall_name'),
                $user);

        return $user;
    }

    protected function sendSignupNotificationEmail($listing, $to, $link)
    {
        $directoryTitle = $this->container->getParameter('ccetc_directory.title');
        
        $content = $this->container->get('templating')->render('CCETCDirectoryBundle:Directory:_new_listing_admin_email.html.twig', array(
            'listing' => $listing,
            'link' => $link,
            'directoryTitle' => $directoryTitle
        ));
        
        $message = \Swift_Message::newInstance()
                ->setSubject($directoryTitle.' - Sign Up')
                ->setFrom('noreply@ccetompkins.org')
                ->setTo($to)
                ->setContentType('text/html')
                ->setBody($content)
        ;
        $this->container->get('mailer')->send($message);
    }

    protected function sendWelcomeEmail($listing)
    {
        $directoryTitle = $this->container->getParameter('ccetc_directory.title');
        $contactEmail = $this->container->getParameter('ccetc_directory.contact_email');

        $listingTypeHelper = $this->container->get('ccetc.directory.helper.listingtypehelper');
        $listingType = $listingTypeHelper->findOneByEntityClassPath("\\".get_class($listing));

        if(count($listingTypeHelper->getAll()) > 1) {
            $template = 'CCETCDirectoryBundle:Directory:_'.$listingType->getKey().'_welcome_email.html.twig';
        }
        if(!isset($template) || !$this->container->get('templating')->exists($template) ) {
            $template = 'CCETCDirectoryBundle:Directory:_welcome_email.html.twig';
        }

        $content = $this->container->get('templating')->render($template, array(
            'listing' => $listing,
            'directoryTitle' => $directoryTitle,
            'contactEmail' => $contactEmail,
            'loginLink' => $this->getPageLink().$this->container->get('router')->generate('fos_user_security_login'),
            'editLink' => $this->getPageLink().$this->container->get('router')->generate($listingType->getEditRouteName(), array('id' => $listing->getId()))
        ));
        
        $message = \Swift_Message::newInstance()
                ->setSubject('Welcome to '.$directoryTitle."!")
                ->setFrom('noreply@ccetompkins.org')
                ->setTo($listing->getPrimaryEmail())
                ->setContentType('text/html')
                ->setBody($content)
        ;
        $this->container->get('mailer')->send($message);
    }
    
    protected function getPageLink()
    {
        $httpHost = $this->container->get('request')->getHttpHost();
        return 'http://' . $httpHost;
    }   
    
}
