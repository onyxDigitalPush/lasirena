<?php

namespace App\Models\EmailingApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SendingQueue extends Model
{
    use HasFactory;

    //override the database prefix


    protected $table = 'sending_queue';

    protected $primaryKey = 'queue_id';

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'newsletter_reference',
        'sending_date',
        'sender',
        'email_recipient',
        'email_replyto',
        'subject',
        'message'
    ];

    public function storeQueueEmails($newsletter_reference, $sending_date, $sender, $email_recipient, $email_replyto, $subject, $message)
    {
        SendingQueue::create([
            'newsletter_reference' => $newsletter_reference,
            'sending_date' => $sending_date,
            'sender' => $sender,
            'email_recipient' => $email_recipient,
            'email_replyto' => $email_replyto,
            'subject' => $subject,
            'message' => $message
        ]);
    }
}
