<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

#[AsCommand(
    name: 'app:test-email',
    description: 'Test email sending with Resend',
)]
class TestEmailCommand extends Command
{
    public function __construct(
        private MailerInterface $mailer,
        private string $mailerFromEmail,
        private string $mailerFromName
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('to', InputArgument::REQUIRED, 'Recipient email address')
            ->setHelp('This command allows you to send a test email using Resend')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $toEmail = $input->getArgument('to');

        $io->title('Sending test email with Resend');
        
        $io->section('Configuration');
        $io->text([
            sprintf('From: %s <%s>', $this->mailerFromName, $this->mailerFromEmail),
            sprintf('To: %s', $toEmail),
        ]);

        try {
            $email = (new Email())
                ->from(new Address($this->mailerFromEmail, $this->mailerFromName))
                ->to($toEmail)
                ->subject('Test Email from Gearforge')
                ->text('This is a test email sent via Resend!')
                ->html('<p>This is a <strong>test email</strong> sent via Resend!</p>');

            $io->section('Sending...');
            $this->mailer->send($email);

            $io->success('Email sent successfully!');
            $io->note('Check your inbox and the Resend dashboard: https://resend.com/emails');
            
            // Try to get message ID if available
            $io->section('Debug Info');
            $io->text([
                'Mailer class: ' . get_class($this->mailer),
                'Email from: ' . $this->mailerFromEmail,
                'Environment: ' . ($_ENV['APP_ENV'] ?? 'unknown'),
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to send email: ' . $e->getMessage());
            $io->section('Error Details');
            $io->text([
                'Exception class: ' . get_class($e),
                'Message: ' . $e->getMessage(),
                'Code: ' . $e->getCode(),
            ]);
            
            if ($output->isVerbose()) {
                $io->section('Stack Trace');
                $io->text($e->getTraceAsString());
            }
            
            return Command::FAILURE;
        }
    }
}
