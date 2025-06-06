<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Product;
use App\Models\TaskProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ProjectPermission;

class TaskProductController extends Controller
{
    /**
     * Lấy danh sách vật tư của công việc
     *
     * @param Task $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Task $task)
    {
        if (!ProjectPermission::hasPermissionInProject('tasks.resources', $task->project_id)) {
            return response()->json(['error' => 'Bạn không có quyền xem vật tư công việc trong dự án này!'], 403);
        }
        $products = $task->products()
            ->with(['unit', 'category'])
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'code' => $product->code,
                    'name' => $product->name,
                    'quantity' => $product->pivot->quantity,
                    'notes' => $product->pivot->notes,
                    'duration' => $product->pivot->duration,
                    'unit' => $product->unit ? $product->unit->name : '',
                    'category' => $product->category ? $product->category->name : '',
                    'export_price' => $product->export_price,
                    'total_price' => $product->export_price * $product->pivot->quantity,
                ];
            });

        return response()->json($products);
    }

    /**
     * Thêm vật tư vào công việc
     *
     * @param Request $request
     * @param Task $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Task $task)
    {
        if (!ProjectPermission::hasPermissionInProject('tasks.resources', $task->project_id)) {
            return response()->json(['error' => 'Bạn không có quyền thêm vật tư vào công việc trong dự án này!'], 403);
        }
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'duration' => 'required|integer|min:1',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Kiểm tra xem vật tư đã được thêm vào công việc chưa
        $existingProduct = $task->products()->where('product_id', $request->product_id)->first();

        if ($existingProduct) {
            return response()->json(['message' => 'Vật tư đã được thêm vào công việc này'], 422);
        }

        // Thêm vật tư vào công việc
        $task->products()->attach($request->product_id, [
            'duration' => $request->duration,
            'quantity' => $request->quantity,
            'notes' => $request->notes,
            'created_by' => auth()->id(),
        ]);

        // Lấy thông tin vật tư vừa thêm
        $product = Product::with(['unit', 'category'])->find($request->product_id);

        return response()->json([
            'id' => $product->id,
            'code' => $product->code,
            'name' => $product->name,
            'quantity' => $request->quantity,
            'notes' => $request->notes,
            'unit' => $product->unit ? $product->unit->name : '',
            'category' => $product->category ? $product->category->name : '',
            'export_price' => $product->export_price,
            'total_price' => $product->export_price * $request->quantity,
        ]);
    }

    /**
     * Cập nhật thông tin vật tư trong công việc
     *
     * @param Request $request
     * @param Task $task
     * @param Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Task $task, Product $product)
    {
        if (!ProjectPermission::hasPermissionInProject('tasks.resources', $task->project_id)) {
            return response()->json(['error' => 'Bạn không có quyền cập nhật vật tư công việc trong dự án này!'], 403);
        }
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|numeric|min:0.01',
            'duration' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Kiểm tra xem vật tư có trong công việc không
        $existingProduct = $task->products()->where('product_id', $product->id)->first();

        if (!$existingProduct) {
            return response()->json(['message' => 'Vật tư không tồn tại trong công việc này'], 404);
        }

        // Cập nhật thông tin vật tư
        $task->products()->updateExistingPivot($product->id, [
            'duration' => $request->duration,
            'quantity' => $request->quantity,
            'notes' => $request->notes,
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'id' => $product->id,
            'code' => $product->code,
            'name' => $product->name,
            'quantity' => $request->quantity,
            'duration' => $request->duration,
            'notes' => $request->notes,
            'unit' => $product->unit ? $product->unit->name : '',
            'category' => $product->category ? $product->category->name : '',
            'export_price' => $product->export_price,
            'total_price' => $product->export_price * $request->quantity,
        ]);
    }

    /**
     * Xóa vật tư khỏi công việc
     *
     * @param Task $task
     * @param Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Task $task, Product $product)
    {
        if (!ProjectPermission::hasPermissionInProject('tasks.resources', $task->project_id)) {
            return response()->json(['error' => 'Bạn không có quyền xóa vật tư công việc trong dự án này!'], 403);
        }
        // Xóa vật tư khỏi công việc
        $task->products()->detach($product->id);

        return response()->json(['success' => true]);
    }
}
