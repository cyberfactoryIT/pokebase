<?php
namespace App\Http\Controllers;

use App\Models\Faq;
use App\Http\Requests\StoreFaqRequest;
use App\Http\Requests\UpdateFaqRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FaqController extends Controller
{
    public function index()
    {
        $faqs = Faq::ordered()->get();
        return view('faqs.index', compact('faqs'));
    }

    public function create()
    {
        return view('faqs.form');
    }

    public function store(StoreFaqRequest $request)
    {
        Faq::create($request->validated());
        return redirect()->route('faqs.index');
    }

    public function edit(Faq $faq)
    {
        return view('faqs.form', compact('faq'));
    }

    public function update(UpdateFaqRequest $request, Faq $faq)
    {
        $faq->update($request->validated());
        return redirect()->route('faqs.index');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();
        return redirect()->route('faqs.index');
    }
    public function reorder(Request $request)
    {
        foreach ($request->input('order', []) as $item) {
            Faq::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }
        return response()->json(['status' => 'ok']);
    }

    public function togglePublish(Faq $faq)
    {
        $faq->is_published = !$faq->is_published;
        if ($faq->is_published && !$faq->published_at) {
            $faq->published_at = Carbon::now();
        }
        $faq->save();
        return response()->json(['status' => 'ok', 'is_published' => $faq->is_published]);
    }
}
