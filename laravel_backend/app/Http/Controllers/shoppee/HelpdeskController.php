<?php
namespace App\Http\Controllers\Shoppee;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shoppee_Helpdesk;
class HelpdeskController extends Controller
{
    // Create Helpdesk Ticket (Shoppe sends message)
   public function store(Request $request)
{
    $request->validate([
        'user_id' => 'required',
        'subject' => 'required',
        'message' => 'required',
    ]);

    $helpdesk = Shoppee_Helpdesk::create([
        'user_id' => $request->user_id,
        'subject' => $request->subject,
        'message' => $request->message,
        'status' => 'closed'
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Helpdesk request submitted successfully',
        'data' => $helpdesk
    ]);
}
    // Get User Helpdesk Tickets
    public function userHelpdesk($user_id)
    {
        $tickets = Shoppee_Helpdesk::where('user_id', $user_id)->latest()->get();
        return response()->json($tickets);
    }
    // Admin Reply
    public function reply(Request $request, $id)
    {
        $ticket = Shoppee_Helpdesk::find($id);
        $ticket->admin_reply = $request->admin_reply;
        // When admin replies, ticket becomes OPEN for the user
        $ticket->status = "open";
        $ticket->save();
        return response()->json([
            'message' => 'Reply sent successfully'
        ]);
    }
}