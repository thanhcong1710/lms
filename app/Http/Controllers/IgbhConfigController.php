<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IgbhConfigController extends Controller
{
    public function getConfig(Request $request, $testSeq)
    {
        $user = \App\Http\Controllers\AuthController::resolveUser($request);
        if (!$user || !$user->isAdmin()) {
            return response()->json(['message' => 'Forbidden. Admin only.'], 403);
        }

        $test = DB::table('lms_tests')->where('test_seq', $testSeq)->where('test_type', 'IG.BH')->first();
        if (!$test) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $type = (stripos($test->name, 'summative') !== false) ? 'summative' : 'diagnostic';

        if ($type === 'summative') {
            $themes = DB::table('igbh_summative_themes')->where('test_seq', $testSeq)->orderBy('sort_no')->get();
            return response()->json([
                'type' => 'summative',
                'test' => $test,
                'themes' => $themes
            ]);
        } else {
            $configRow = DB::table('igbh_test_configs')->where('test_seq', $testSeq)->first();
            $questions = DB::table('igbh_test_questions')->where('test_seq', $testSeq)->orderBy('sort_no')->get();
            foreach ($questions as $q) {
                if ($q->areas) {
                    $decoded = json_decode($q->areas, true);
                    $q->areas = is_array($decoded) ? $decoded : [];
                } else {
                    $q->areas = [];
                }
            }
            $comments = DB::table('igbh_test_comments')->where('test_seq', $testSeq)->get();
            
            // Default config if missing
            $config = $configRow ? json_decode($configRow->sectors, true) : [
                'A' => '', 'B' => '', 'C' => '', 'D' => '', 'E' => ''
            ];

            return response()->json([
                'type' => 'diagnostic',
                'test' => $test,
                'config' => $config,
                'questions' => $questions,
                'comments' => $comments
            ]);
        }
    }

    public function updateConfig(Request $request, $testSeq)
    {
        $user = \App\Http\Controllers\AuthController::resolveUser($request);
        if (!$user || !$user->isAdmin()) {
            return response()->json(['message' => 'Forbidden. Admin only.'], 403);
        }

        $type = $request->input('type');
        
        if ($type === 'summative') {
            $themes = $request->input('themes', []);
            DB::beginTransaction();
            try {
                DB::table('igbh_summative_themes')->where('test_seq', $testSeq)->delete();
                $insertData = [];
                foreach ($themes as $index => $theme) {
                    $insertData[] = [
                        'test_seq' => $testSeq,
                        'sort_no' => $index + 1,
                        'theme_desc' => $theme['theme_desc'] ?? '',
                        'theme_point' => $theme['theme_point'] ?? 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
                if (count($insertData) > 0) {
                    DB::table('igbh_summative_themes')->insert($insertData);
                }
                DB::commit();
                return response()->json(['message' => 'Updated successfully']);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['message' => $e->getMessage()], 500);
            }
        } else {
            $config = $request->input('config', []);
            $questions = $request->input('questions', []);
            $comments = $request->input('comments', []);
            
            DB::beginTransaction();
            try {
                // Update sectors
                $existingConfig = DB::table('igbh_test_configs')->where('test_seq', $testSeq)->first();
                if ($existingConfig) {
                    DB::table('igbh_test_configs')->where('test_seq', $testSeq)->update([
                        'sectors' => json_encode($config, JSON_UNESCAPED_UNICODE),
                        'updated_at' => now()
                    ]);
                } else {
                    DB::table('igbh_test_configs')->insert([
                        'test_seq' => $testSeq,
                        'sectors' => json_encode($config, JSON_UNESCAPED_UNICODE),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }

                // Update questions
                DB::table('igbh_test_questions')->where('test_seq', $testSeq)->delete();
                $insertQuestions = [];
                foreach ($questions as $index => $q) {
                    // Re-index sort_no
                    $insertQuestions[] = [
                        'test_seq' => $testSeq,
                        'question_type' => $q['question_type'] ?? 'curriculum',
                        'sort_no' => $index + 1,
                        'sector' => $q['sector'] ?? null,
                        'type_cd' => $q['type_cd'] ?? null,
                        'answer' => $q['answer'] ?? null,
                        'areas' => is_array($q['areas'] ?? null) ? json_encode($q['areas']) : $q['areas'],
                        'difficulty' => $q['difficulty'] ?? null,
                        'standard_point' => $q['standard_point'] ?? 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
                if (count($insertQuestions) > 0) {
                    DB::table('igbh_test_questions')->insert($insertQuestions);
                }
                
                // Update comments
                DB::table('igbh_test_comments')->where('test_seq', $testSeq)->delete();
                $insertComments = [];
                foreach ($comments as $c) {
                    $insertComments[] = [
                        'test_seq' => $testSeq,
                        'comment_type' => $c['comment_type'] ?? 'total',
                        'condition_value' => $c['condition_value'] ?? '',
                        'good_comment' => $c['good_comment'] ?? '',
                        'weak_comment' => $c['weak_comment'] ?? '',
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
                if (count($insertComments) > 0) {
                    DB::table('igbh_test_comments')->insert($insertComments);
                }

                DB::commit();
                return response()->json(['message' => 'Updated successfully']);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['message' => $e->getMessage()], 500);
            }
        }
    }
}
