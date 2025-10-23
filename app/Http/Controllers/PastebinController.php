<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Pastebin;
use Illuminate\Support\Facades\Hash;

class PastebinController extends Controller
{

    /**
     * Home page
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('Pastebin.index');
    }

    public function viewPastebin(string $hash)
    {
        $pastebin = Pastebin::where('hash', $hash)->first();

        if (!$pastebin) {
            return $this->notFound();
        }
        
        return view('Pastebin.view')->with('pastebin', $pastebin);
    }

    /**
     * When the user attempts to visit a pastebin that either does not exist, has expired, or was deleted.
     * 
     * @return \Illuminate\Contracts\View\View
     */
    public function notFound()
    {
        return view('Pastebin.notfound');
    }

    /**
     * Handles form submit for new pastebin.
     * 
     * @param \Illuminate\Http\Request $request
     */
    public function handleNewPastebin(Request $request)
    {

        $rules = [
            'title' => 'nullable|string|max:255',
            'content' => 'required|string',
            'language' => 'required|string|in:text,markdown,php,javascript,json,python,php,html,css,sql,bash',
            'expires' => 'required|string|in:never,10min,1hr,1day,1week,1month',
            'visibility' => 'required|string|in:public,unlisted,private',
            'password' => 'nullable|string|min:6|max:40'
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();  
        }

        $validated = $validator->validated();

        $title = $validated['title'] ?? null;
        $content = $validated['content'];
        $language = $validated['language'];
        $expires = $validated['expires'];
        $visibility = $validated['visibility'];
        $password = $validated['password'] ?? null;

        $pastebin = new Pastebin();
        $pastebin->title = $title;
        $pastebin->content = htmlentities($content);
        $pastebin->language = $language;
        $pastebin->expires_at = $this->calculateExpiryTimestamp($expires);
        $pastebin->visibility = $visibility;
        $pastebin->hash = $pastebin->generateHash();
        $pastebin->password = Hash::make($password);
        $pastebin->save();

        return redirect()->route('viewPastebin', ['hash' => $pastebin->hash]);
    }

    /**
     * 
     * converts expiration from form data into DateTime to be stored in DB
     *  
     * @param string $expires
     * @return int|null
     */
    protected function calculateExpiryTimestamp(string $expires)
    {
        switch ($expires) {
            case '10min':
                return now()->addMinutes(10)->timestamp;
            case '1hr':
                return now()->addHour()->timestamp;
            case '1day':
                return now()->addDay()->timestamp;
            case '1week':
                return now()->addWeek()->timestamp;
            case 'never':
            default:
                return null;
        }
    }
}