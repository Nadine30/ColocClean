<?php

namespace App\Http\Controllers;

use App\Models\Colocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ColocationController extends Controller
{
    /**
     * Affiche les colocations de l'utilisateur et le formulaire créer / rejoindre.
     */
    public function index(Request $request)
    {
        $colocations = $request->user()->colocations()->with('owner')->get();

        return view('colocations.index', compact('colocations'));
    }

    /**
     * Crée une nouvelle colocation.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $userId = $request->user()->id;
        $data = [
            'name' => $validated['name'],
            'invitation_code' => Colocation::generateInvitationCode(),
            'owner_id' => $userId,
        ];
        if (Schema::hasColumn('colocations', 'user_id')) {
            $data['user_id'] = $userId;
        }
        $colocation = Colocation::create($data);

        $colocation->members()->attach($request->user()->id);

        return redirect()
            ->route('colocations.show', $colocation)
            ->with('success', 'Colocation créée. Partagez le code d\'invitation : ' . $colocation->invitation_code);
    }

    /**
     * Rejoindre une colocation avec le code d'invitation.
     */
    public function join(Request $request)
    {
        $validated = $request->validate([
            'invitation_code' => ['required', 'string', 'size:6'],
        ]);

        $code = strtoupper($validated['invitation_code']);
        $colocation = Colocation::where('invitation_code', $code)->first();

        if (! $colocation) {
            throw ValidationException::withMessages([
                'invitation_code' => ['Ce code d\'invitation est invalide.'],
            ]);
        }

        if ($colocation->hasMember($request->user())) {
            return redirect()
                ->route('colocations.show', $colocation)
                ->with('info', 'Vous faites déjà partie de cette colocation.');
        }

        $colocation->members()->attach($request->user()->id);

        return redirect()
            ->route('colocations.show', $colocation)
            ->with('success', 'Vous avez rejoint la colocation "' . $colocation->name . '".');
    }

    /**
     * Affiche une colocation (détail + membres).
     */
    public function show(Request $request, Colocation $colocation)
    {
        if (! $colocation->hasMember($request->user())) {
            abort(403, 'Vous n\'avez pas accès à cette colocation.');
        }

        $colocation->load('members', 'owner');

        return view('colocations.show', compact('colocation'));
    }
}
