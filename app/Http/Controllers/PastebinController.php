<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Pastebin;

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
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            dd($validator->errors());
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();  
        }

        $validated = $validator->validated();

        $title = $validated['title'];
        $content = $validated['content'];
        $language = $validated['language'];
        $expires = $validated['expires'];
        $visibility = $validated['visibility'];

        $pastebin = new Pastebin();
        $pastebin->title = $title;
        $pastebin->content = htmlentities($content);
        $pastebin->language = $language;
        $pastebin->expires_at = $this->calculateExpiryTimestamp($expires);
        $pastebin->hash = $pastebin->generateHash();
        $pastebin->save();

        dd($pastebin);
    }

    /**
     * 
     * converts expiration from form data into DateTime to be stored in DB
     *  
     * @param string $expires
     * @return \DateTime|null
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