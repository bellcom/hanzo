<?php /* vim: set sw=4: */

namespace Hanzo\Bundle\DataIOBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Loader\XliffFileLoader;

class RunChecksCommand extends ContainerAwareCommand
{

    protected $errors = [];

    protected function configure()
    {
        $this->setName('hanzo:run-checks')
            ->setDescription('Run checks and tests before "allowing" deploy')
            ->addArgument('email', InputArgument::OPTIONAL, 'Email address of the one testing, if set only this person will get a status mail.')
        ;
    }


    /**
     * executes the job
     *
     * TODO: seperate validators into services
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->validateXliff();

        // must be last!
        $this->sendMail($input->getArgument('email'));
    }

    /**
     * validate xliff files for schematic errors
     */
    protected function validateXliff()
    {
        $dir = $this->getContainer()->get('kernel')->getRootDir() . '/Resources/translations';
        $finder = Finder::create()->files()->name('*.xliff')->in($dir);

        foreach ($finder as $file) {
            $path = $file->getRealPath();
            $name = $file->getFilename();
            list($domain, $locale, $junk) = explode('.', $name);

            try {
                $parser = new XliffFileLoader();
                $parser->load($path, $locale, $domain);
            } catch (\RuntimeException $e) {
                $this->errors[] = $name . ":\n" .$e->getMessage()."\n";
            }
        }
    }


    /**
     * send "go" or "no go" mail
     *
     * @param  string $email optional override email
     */
    protected function sendMail($email)
    {
        $type = 'GO!';
        if (count($this->errors)) {
            $type = 'NO GO!';
        }

        $recipients = [
            'hd@pompdelux.dk',
            'lv@POMPdeLUX.dk',
            'un@bellcom.dk',
            'mmh@bellcom.dk',
            'ab@bellcom.dk',
        ];

        if ($email) {
            $recipients = [$email];
        }

        if ('GO!' == $type){
            $text = "Alle pre-deploy checks ok, der må deployes!";
        } else {
            $recipients[] = 'pdl@bellcom.dk';
            $text = "Såskudaogs! Der er fejl i skidtet, der må IKKE deployes!\n\n".implode("\n", $this->errors);
        }

        $text .= "\n\nmvh\n-- \nMr. Miyagi";

        $message = \Swift_Message::newInstance()
            ->setSubject('Hanzo deploy validation: '.$type)
            ->setFrom('pompdelux@pompdelux.dk')
            ->setReturnPath('pompdelux@pompdelux.dk')
            ->setTo($recipients)
            ->setBody($text)
        ;

        $this->getContainer()->get('mailer')->send($message);
    }
}
