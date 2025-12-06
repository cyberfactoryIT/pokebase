<?php
// app/Http/Controllers/Admin/HelpController.php
namespace App\Http\Controllers\Admin;

    use App\Http\Controllers\Controller;
    use App\Models\Help;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    public function index() {
        $helps = Help::orderBy('key')->paginate(20);
        return view('superadmin.helps.index', compact('helps'));
    }
    public function create() { return view('superadmin.helps.form', ['help'=>new Help]); }
    public function store(Request $r) {
        $data = $this->validateData($r);
        Help::create($data);
        return redirect()->route('helps.index')->with('ok','Created');
    }
    public function edit(Help $help) { return view('superadmin.helps.form', compact('help')); }
    public function update(Request $r, Help $help) {
        $help->update($this->validateData($r));
        return back()->with('ok','Updated');
    }
    public function destroy(Help $help) { $help->delete(); return back()->with('ok','Deleted'); }

    private function validateData(Request $r): array {
        return $r->validate([
            'key'   => 'required|string|max:128',
            'icon'  => 'nullable|string|max:64',
            'title' => 'nullable|array',
            'title.en' => 'nullable|string',
            'title.it' => 'nullable|string',
            'title.da' => 'nullable|string',
            'short' => 'nullable|array',
            'short.en' => 'nullable|string',
            'short.it' => 'nullable|string',
            'short.da' => 'nullable|string',
            'long'  => 'nullable|array',
            'long.en' => 'nullable|string',
            'long.it' => 'nullable|string',
            'long.da' => 'nullable|string',
            'links' => 'nullable|array',
            'links.*.route' => 'nullable|string',
            'links.*.url' => 'nullable|url',
            'links.*.label' => 'nullable|array',
            'links.*.label.en' => 'nullable|string',
            'links.*.label.it' => 'nullable|string',
            'links.*.label.da' => 'nullable|string',
            'meta'  => 'nullable|array',
            'is_active' => 'boolean',
        ]);
    }
}
