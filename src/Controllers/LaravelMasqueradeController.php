<?php

namespace Chadhurin\LaravelMasquerade\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Chadhurin\LaravelMasquerade\LaravelMasquerade;

class LaravelMasqueradeController extends Controller
{
    /** @var LaravelMasquerade */
    protected mixed $manager;

    /**
     * LaravelMasqueradeController constructor.
     */
    public function __construct()
    {
        $this->manager = app()->make(LaravelMasquerade::class);

        $guard = $this->manager->getDefaultSessionGuard();
        $this->middleware('auth:' . $guard)->only('take');
    }


    /**
     * @param int $id
     * @param string|null $guardName
     *
     * @return  RedirectResponse
     * @throws  \Exception
     */
    public function take(Request $request, $id, $guardName = null)
    {
        $guardName = $guardName ?? $this->manager->getDefaultSessionGuard();

        // Cannot masquerade yourself
        if ($id == $request->user()->getAuthIdentifier() && ($this->manager->getCurrentAuthGuardName() == $guardName)) {
            abort(403);
        }

        // Cannot masquerade again if you're already masquerading a user
        if ($this->manager->isMasquerading()) {
            abort(403);
        }

        if (!$request->user()->canMasquerade()) {
            abort(403);
        }

        $userToMasquerade = $this->manager->findUserById($id, $guardName);

        if ($userToMasquerade->canBeMasqueraded()) {
            if ($this->manager->take($request->user(), $userToMasquerade, $guardName)) {

                $takeRedirect = $this->manager->getTakeRedirectTo();

                if ($takeRedirect !== 'back') {

                    return redirect()->to($takeRedirect);
                }
            }
        }

        return redirect()->back();
    }

    /**
     * @return RedirectResponse
     */
    public function leave()
    {
        if (!$this->manager->isMasquerading()) {
            abort(403);
        }

        $this->manager->leave();

        $leaveRedirect = $this->manager->getLeaveRedirectTo();
        if ($leaveRedirect !== 'back') {
            return redirect()->to($leaveRedirect);
        }
        return redirect()->back();
    }
}