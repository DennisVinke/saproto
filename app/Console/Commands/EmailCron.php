<?php

namespace Proto\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

use Proto\Models\Email;

use Mail;

class EmailCron extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'proto:emailcron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cronjob that sends all admin created e-mails';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {

        // Send admin created e-mails.
        $emails = Email::where('sent', false)->where('ready', true)->where('time', '<', date('U'))->get();
        $this->info('There are ' . $emails->count() . ' queued e-mails.');

        foreach ($emails as $email) {

            $this->info('Sending e-mail <' . $email->subject . '>');

            $email->ready = false;
            $email->sent = true;
            $email->sent_to = $email->recipients()->count();
            $email->save();

            $emaildata = (object)[
                'sender_address' => $email->sender_address,
                'sender_name' => $email->sender_name,
                'subject' => $email->subject,
                'attachments' => $email->attachments
            ];

            foreach ($email->recipients() as $recipient) {

                Mail::queueOn('medium', 'emails.manualemail', [
                    'body' => $email->parseBodyFor($recipient),
                    'attachments' => $email->attachments,
                    'destination' => $email->destinationForBody(),
                    'user_id' => $recipient->id,
                    'event_name' => $email->getEventName()
                ], function ($message) use ($emaildata, $recipient) {

                    $message
                        ->to($recipient->email, $recipient->name)
                        ->from($emaildata->sender_address . '@' . config('proto.emaildomain'), $emaildata->sender_name)
                        ->subject($emaildata->subject);

                    foreach ($emaildata->attachments as $attachment) {
                        $message->attach($attachment->generateLocalPath(), ['as' => $attachment->original_filename, 'mime' => $attachment->mime]);
                    }

                });

            }

            $this->info('Sent to ' . $email->recipients()->count() . ' people.');

        }

        $this->info(($emails->count() > 0 ? 'All e-mails sent.' : 'No e-mails to be sent.'));

    }

}
