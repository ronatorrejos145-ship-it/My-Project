<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\ServiceApplication;
use App\Models\TechnicalSurvey;
use App\Models\TechnicalSurveyMeasurement;
use App\Models\Employee;
use App\Services\TechnicalSurveyService;
use App\Services\TechnicalSurveyEvaluationService;
use App\Services\SurveyApprovalService;
use App\Services\SurveyReportPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase7TechnicalSurveyTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $techUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->adminUser = User::factory()->create(['status' => 'ACTIVE']);
        $adminRole = Role::where('code', 'SUPER_ADMIN')->first();
        if ($adminRole) {
            $this->adminUser->roles()->attach($adminRole->id);
        }

        $this->techUser = User::factory()->create(['status' => 'ACTIVE']);
        $techRole = Role::where('code', 'TECHNICAL')->first();
        if ($techRole) {
            $this->techUser->roles()->attach($techRole->id);
        }
    }

    public function test_survey_creation_generates_sequential_number()
    {
        $app = ServiceApplication::first();
        $surveyService = app(TechnicalSurveyService::class);

        $survey = $surveyService->createSurveyForApplication($app);

        $this->assertNotEmpty($survey->survey_number);
        $this->assertStringStartsWith('SUR-', $survey->survey_number);
        $this->assertDatabaseHas('technical_surveys', ['id' => $survey->id]);
    }

    public function test_gps_arrival_verification_calculates_distance()
    {
        $survey = TechnicalSurvey::first();
        $surveyService = app(TechnicalSurveyService::class);

        // Technician arrives at 14.6520000, 121.0320000
        $updated = $surveyService->verifyGpsArrival($survey, 14.6520000, 121.0320000, 3.0);

        $this->assertEquals('ARRIVED_AT_SITE', $updated->arrival_verification_status);
        $this->assertNotNull($updated->arrival_distance_meters);
    }

    public function test_evaluation_engine_evaluates_line_of_sight_and_safety()
    {
        $survey = TechnicalSurvey::first();
        $evalService = app(TechnicalSurveyEvaluationService::class);

        $survey->update([
            'line_of_sight_status' => 'CLEAR',
            'safety_assessment' => 'SAFE',
            'installation_complexity' => 'NORMAL',
        ]);

        $eval = $evalService->evaluateSurvey($survey);

        $this->assertEquals('RECOMMENDED', $eval['recommendation']);
        $this->assertEquals('TECHNICALLY_FEASIBLE', $eval['final_decision']);
    }

    public function test_supervisor_approval_updates_survey_and_application_handoff()
    {
        $survey = TechnicalSurvey::first();
        $approvalService = app(SurveyApprovalService::class);

        $updated = $approvalService->reviewSurvey(
            $survey,
            'APPROVED',
            'Site inspection confirmed optical power -18.5 dBm and safe roof access'
        );

        $this->assertEquals('APPROVED', $updated->status);
        $this->assertEquals('APPROVED', $survey->application->fresh()->status);
    }

    public function test_pdf_report_html_generation()
    {
        $survey = TechnicalSurvey::first();
        $pdfService = app(SurveyReportPdfService::class);

        $html = $pdfService->generateReportHtml($survey);

        $this->assertStringContainsString('TECHNICAL FIELD SURVEY REPORT', $html);
        $this->assertStringContainsString($survey->survey_number, $html);
    }

    public function test_admin_can_view_technical_surveys_queue()
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.technical-surveys.index'));
        $response->assertStatus(200);

        $survey = TechnicalSurvey::first();
        $responseShow = $this->actingAs($this->adminUser)->get(route('admin.technical-surveys.show', $survey));
        $responseShow->assertStatus(200);
        $responseShow->assertSee($survey->survey_number);
    }

    public function test_migration_rollback_and_reapply_cleanly()
    {
        $this->artisan('migrate:rollback')
            ->assertExitCode(0);

        $this->artisan('migrate')
            ->assertExitCode(0);
    }
}
