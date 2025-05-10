<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\JobComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class JobCommentController extends Controller
{
    /**
     * Store a newly created comment in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $reportId
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $reportId)
    {
        // Log the incoming request
        Log::info('Comment store request', [
            'reportId' => $reportId,
            'user_id' => Auth::id(),
            'request_data' => $request->all()
        ]);
        
        $request->validate([
            'comment' => 'required|string',
        ]);
        
        $dailyReport = DailyReport::findOrFail($reportId);
        
        $comment = new JobComment([
            'comment' => $request->comment,
            'visibility' => 'public',
            'user_id' => Auth::id(),
        ]);
        
        try {
            $dailyReport->comments()->save($comment);
            
            Log::info('Comment saved successfully', [
                'comment_id' => $comment->id,
                'daily_report_id' => $dailyReport->id
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'comment' => [
                        'id' => $comment->id,
                        'comment' => $comment->comment,
                        'visibility' => 'public',
                        'created_at' => $comment->created_at->diffForHumans(),
                        'formatted_date' => $comment->created_at->format('M d, Y h:i A'),
                        'user' => [
                            'id' => Auth::id(),
                            'name' => Auth::user()->name,
                            'profile_picture' => Auth::user()->profile_picture ? asset('storage/' . Auth::user()->profile_picture) : null,
                        ],
                        'is_owner' => true,
                    ],
                ]);
            }
            
            return redirect()->back()->with('success', 'Comment added successfully.');
        } catch (\Exception $e) {
            Log::error('Error saving comment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error saving comment: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Error adding comment: ' . $e->getMessage());
        }
    }
    
    /**
     * Get comments for a daily report.
     *
     * @param  int  $reportId
     * @return \Illuminate\Http\Response
     */
    public function getComments($reportId)
    {
        try {
            Log::info('Fetching comments', ['reportId' => $reportId, 'user_id' => Auth::id()]);
            
            $dailyReport = DailyReport::findOrFail($reportId);
            
            // Get all comments regardless of visibility
            $comments = $dailyReport->comments()
                ->with('user')
                ->orderBy('created_at', 'desc')
                ->get();
            
            Log::info('Comments fetched successfully', [
                'reportId' => $reportId, 
                'count' => $comments->count()
            ]);
                
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'comments' => $comments->map(function($comment) {
                        return [
                            'id' => $comment->id,
                            'comment' => $comment->comment,
                            'visibility' => 'public',
                            'created_at' => $comment->created_at->diffForHumans(),
                            'formatted_date' => $comment->created_at->format('M d, Y h:i A'),
                            'user' => [
                                'id' => $comment->user->id,
                                'name' => $comment->user->name,
                                'profile_picture' => $comment->user->profile_picture ? asset('storage/' . $comment->user->profile_picture) : null,
                            ],
                            'is_owner' => $comment->user_id === Auth::id(),
                        ];
                    }),
                ]);
            }
            
            return view('job-comments.index', compact('dailyReport', 'comments'));
        } catch (\Exception $e) {
            Log::error('Error fetching comments', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error fetching comments: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Error fetching comments: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete a comment.
     *
     * @param  int  $commentId
     * @return \Illuminate\Http\Response
     */
    public function destroy($commentId)
    {
        try {
            Log::info('Attempting to delete comment', ['commentId' => $commentId, 'user_id' => Auth::id()]);
            
            $comment = JobComment::findOrFail($commentId);
            
            // Only allow the comment owner or admin to delete a comment
            if ($comment->user_id !== Auth::id() && Auth::user()->role->slug !== 'admin') {
                Log::warning('Unauthorized delete attempt', [
                    'commentId' => $commentId,
                    'user_id' => Auth::id(),
                    'comment_owner_id' => $comment->user_id
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to delete this comment.',
                ], 403);
            }
            
            $comment->delete();
            
            Log::info('Comment deleted successfully', ['commentId' => $commentId]);
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Comment deleted successfully.',
                ]);
            }
            
            return redirect()->back()->with('success', 'Comment deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting comment', [
                'commentId' => $commentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting comment: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Error deleting comment: ' . $e->getMessage());
        }
    }
}
