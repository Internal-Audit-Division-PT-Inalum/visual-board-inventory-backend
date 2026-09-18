<?php

namespace App\Http\Resources\Api\VisualBoard;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisualBoardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'open_abnormality_count' => $this->resource['open_abnormality_count'],
            'abnormality_resolved_today' => $this->resource['abnormality_resolved_today'] ?? 0,
            'abnormality_in_progress' => $this->resource['abnormality_in_progress'] ?? 0,
            'compliance_percentage' => $this->resource['compliance_percentage'],
            'compliance_mom_trend' => $this->resource['compliance_mom_trend'] ?? 'steady',
            'resolution_rate' => $this->resource['resolution_rate'] ?? 0,
            'resolution_grade' => $this->resource['resolution_grade'] ?? '',
            'audit_pass_percentage' => $this->resource['audit_pass_percentage'] ?? 0,
            'audit_pass_grade' => $this->resource['audit_pass_grade'] ?? '',
            'safety_streak_days' => $this->resource['safety_streak_days'],
            'safety_safe_shifts' => $this->resource['safety_safe_shifts'] ?? 0,
            'kaizen_implemented_count' => $this->resource['kaizen_implemented_count'],
            'kaizen_cost_saving' => $this->resource['kaizen_cost_saving'] ?? 0,
            'resolution_speed_avg_mins' => $this->resource['resolution_speed_avg_mins'] ?? 0,
            'oee_percentage' => $this->resource['oee_percentage'] ?? 0,
            'oee_target' => $this->resource['oee_target'] ?? 0,
            'abnormalities' => $this->resource['abnormalities']->map(function ($abnormality) {
                return [
                    'id' => (string) $abnormality->id,
                    'zone_name' => $abnormality->zone ? $abnormality->zone->name : 'Unknown Zone',
                    'finder_name' => $abnormality->finder_name,
                    'group_name' => $abnormality->group_name,
                    'description' => $abnormality->problem_description ?? '',
                    'countermeasure_plan' => $abnormality->countermeasure_plan,
                    'countermeasure_actual' => $abnormality->countermeasure_actual,
                    'date_found' => $abnormality->date_found ? $abnormality->date_found->format('Y-m-d') : null,
                    'planned_date' => $abnormality->planned_date ? $abnormality->planned_date->format('Y-m-d') : null,
                    'actual_date' => $abnormality->actual_date ? $abnormality->actual_date->format('Y-m-d') : null,
                    'status' => $abnormality->status,
                    'progress_percentage' => $abnormality->progress_percentage,
                    'pic_name' => $abnormality->pic ? $abnormality->pic->name : null,
                    'is_kaizen' => (bool) $abnormality->is_kaizen,
                    'signed_by_staff' => $abnormality->signed_by_staff,
                    'signed_by_ms' => $abnormality->signed_by_ms,
                    'created_at' => $abnormality->created_at ? $abnormality->created_at->toISOString() : now()->toISOString(),
                    'resolved_at' => $abnormality->status === 'resolved' && $abnormality->updated_at ? $abnormality->updated_at->toISOString() : null,
                ];
            }),
            'kaizen_champions' => $this->resource['kaizen_champions'],
            'schedule_matrix' => $this->resource['schedule_matrix'],
            'weekly_trend' => $this->resource['weekly_trend'],
            'zones' => collect($this->resource['zones'] ?? [])->map(function ($zone) {
                return [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'standard_image_url' => $zone->getFirstMediaUrl('standard_images') ?: null,
                    'pic_utama' => $zone->picUtama ? $zone->picUtama->name : null,
                    'pic_pengganti' => $zone->picPengganti ? $zone->picPengganti->name : null,
                ];
            }),
            'trend_matrix' => $this->resource['trend_matrix'] ?? [],
            'reference_docs' => collect($this->resource['reference_docs'] ?? [])->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'title' => $doc->title,
                    'description' => $doc->description,
                    'category' => $doc->category,
                    'document_url' => $doc->getFirstMediaUrl('document'),
                    'mime_type' => $doc->getFirstMedia('document')?->mime_type,
                ];
            }),
        ];
    }
}
