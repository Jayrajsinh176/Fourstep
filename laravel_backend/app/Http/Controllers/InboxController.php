use App\Models\Message;
use Illuminate\Http\Request;

class InboxController extends Controller
{

    public function inbox($user_id)
    {
        $messages = Inbox::where('receiver_id', $user_id)

        ->orderBy('created_at','desc')
        ->get();

        return response()->json($messages);
    }

}
use App\Models\Inbox;

