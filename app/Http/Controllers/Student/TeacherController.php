<?php



namespace App\Http\Controllers\Student;



use App\Http\Controllers\Controller;

use App\Models\Location;
use App\Models\Subject;
use App\Models\TeacherProfile;

use App\Models\TeacherRequest;

use App\Models\TuitionApplication;

use App\Models\TuitionAssignment;

use App\Models\TuitionPost;

use App\Models\User;

use App\Services\UserNotificationService;

use Illuminate\Http\RedirectResponse;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use Illuminate\View\View;



class TeacherController extends Controller

{

    public function index(
        Request $request
    ): View {
        $subjects = Subject::query()
            ->where('status', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $locations = Location::query()
            ->where('status', true)
            ->orderBy('division')
            ->orderBy('district')
            ->orderBy('area')
            ->get();

        $categories = $subjects
            ->pluck('category')
            ->filter()
            ->unique()
            ->values();

        $query = TeacherProfile::query()
            ->with([
                'user',
                'subjects',
                'locations',
            ])
            ->where('is_verified', true)
            ->where('is_available', true)
            ->whereHas(
                'user',
                function ($userQuery) {
                    $userQuery->where(
                        'status',
                        'active'
                    );
                }
            );

        if ($request->filled('search')) {
            $search = trim(
                (string) $request->search
            );

            $query->where(
                function ($q) use ($search) {
                    $q->where(
                        'university',
                        'like',
                        "%{$search}%"
                    )
                        ->orWhere(
                            'department',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhereHas(
                            'user',
                            function ($userQuery) use ($search) {
                                $userQuery->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                );
                            }
                        )
                        ->orWhereHas(
                            'subjects',
                            function ($subjectQuery) use ($search) {
                                $subjectQuery->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                );
                            }
                        );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Subject Category + Subject Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('subject')) {
            $subjectId = (int) $request->subject;
            $category = $request->filled('category')
                ? (string) $request->category
                : null;

            $query->whereHas(
                'subjects',
                function ($subjectQuery) use (
                    $subjectId,
                    $category
                ) {
                    $subjectQuery->where(
                        'subjects.id',
                        $subjectId
                    );

                    if ($category) {
                        $subjectQuery->where(
                            'subjects.category',
                            $category
                        );
                    }
                }
            );
        } elseif ($request->filled('category')) {
            $category = (string) $request->category;

            $query->whereHas(
                'subjects',
                function ($subjectQuery) use ($category) {
                    $subjectQuery->where(
                        'subjects.category',
                        $category
                    );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Division + District + Upazila / Area Filter
        |--------------------------------------------------------------------------
        */

        if (
            $request->filled('division') ||
            $request->filled('district') ||
            $request->filled('location')
        ) {
            $division = $request->filled('division')
                ? (string) $request->division
                : null;

            $district = $request->filled('district')
                ? (string) $request->district
                : null;

            $locationId = $request->filled('location')
                ? (int) $request->location
                : null;

            $query->whereHas(
                'locations',
                function ($locationQuery) use (
                    $division,
                    $district,
                    $locationId
                ) {
                    if ($division) {
                        $locationQuery->where(
                            'locations.division',
                            $division
                        );
                    }

                    if ($district) {
                        $locationQuery->where(
                            'locations.district',
                            $district
                        );
                    }

                    if ($locationId) {
                        $locationQuery->where(
                            'locations.id',
                            $locationId
                        );
                    }
                }
            );
        }

        if ($request->filled('gender')) {
            $query->where(
                'gender',
                $request->gender
            );
        }

        if ($request->filled('teaching_mode')) {
            $mode = $request->teaching_mode;

            $query->where(
                function ($q) use ($mode) {
                    $q->where(
                        'teaching_mode',
                        $mode
                    )
                        ->orWhere(
                            'teaching_mode',
                            'both'
                        );
                }
            );
        }

        $teachers = $query
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view(
            'student.teachers.index',
            compact(
                'teachers',
                'subjects',
                'locations',
                'categories'
            )
        );
    }

    public function show(

        TeacherProfile $teacher

    ): View {

        $teacher->load([

            'user',

            'subjects',

            'locations',

        ]);



        abort_unless(

            $teacher->is_verified &&

            $teacher->is_available &&

            $teacher->user &&

            $teacher->user->status === 'active',

            404

        );



        $user = auth()->user();



        $tuitionPosts = TuitionPost::query()

            ->where(

                'user_id',

                $user->id

            )

            ->where(

                'status',

                'published'

            )

            ->latest()

            ->get();



        $existingRequest = TeacherRequest::query()

            ->where(

                'student_user_id',

                $user->id

            )

            ->where(

                'teacher_profile_id',

                $teacher->id

            )

            ->whereNotNull(

                'tuition_post_id'

            )

            ->whereIn(

                'status',

                [

                    'pending',

                    'accepted',

                ]

            )

            ->latest()

            ->first();



        return view(

            'student.teachers.show',

            compact(

                'teacher',

                'tuitionPosts',

                'existingRequest'

            )

        );

    }



    public function requestTeacher(

        Request $request,

        TeacherProfile $teacher

    ): RedirectResponse {

        $studentProfile = auth()

            ->user()

            ->studentProfile;



        if (

            ! $studentProfile ||

            ! $studentProfile->isCompleteForMarketplace()

        ) {

            return redirect()

                ->route(

                    'student.profile.edit'

                )

                ->with(

                    'error',

                    'Please complete your student / guardian profile before sending a teacher request.'

                );

        }



        $validated = $request->validate([

            'tuition_post_id' => [

                'required',

                'integer',

                'exists:tuition_posts,id',

            ],



            'message' => [

                'nullable',

                'string',

                'max:2000',

            ],

        ]);



        $result = DB::transaction(

            function () use (

                $teacher,

                $validated

            ) {

                /*

                |--------------------------------------------------------------------------

                | Lock Student

                |--------------------------------------------------------------------------

                |

                | Serializes direct-request creation for this student.

                |

                */



                $student = User::query()

                    ->lockForUpdate()

                    ->findOrFail(

                        auth()->id()

                    );



                /*

                |--------------------------------------------------------------------------

                | Lock Teacher Profile

                |--------------------------------------------------------------------------

                */



                $lockedTeacher = TeacherProfile::query()

                    ->with('user')

                    ->lockForUpdate()

                    ->findOrFail(

                        $teacher->id

                    );



                if (

                    ! $lockedTeacher->is_verified ||

                    ! $lockedTeacher->is_available ||

                    ! $lockedTeacher->user ||

                    $lockedTeacher->user->status !== 'active'

                ) {

                    return [

                        'success' => false,

                        'message' =>

                            'This teacher is currently unavailable for direct tuition requests.',

                    ];

                }



                /*
                |--------------------------------------------------------------------------
                | Required Tuition
                |--------------------------------------------------------------------------
                */

                $lockedTuition = TuitionPost::query()
                    ->lockForUpdate()
                    ->find(
                        $validated['tuition_post_id']
                    );

                if (
                    ! $lockedTuition ||
                    $lockedTuition->user_id !== $student->id ||
                    $lockedTuition->status !== 'published'
                ) {
                    return [
                        'success' => false,
                        'message' => 'The selected tuition post is not available.',
                    ];
                }

                $assignmentExists = TuitionAssignment::query()
                    ->where(
                        'tuition_post_id',
                        $lockedTuition->id
                    )
                    ->exists();

                if ($assignmentExists) {
                    return [
                        'success' => false,
                        'message' => 'This tuition already has a final teacher assignment.',
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | Close Legacy General Requests
                |--------------------------------------------------------------------------
                */

                $legacyRequests = TeacherRequest::query()
                    ->where(
                        'student_user_id',
                        $student->id
                    )
                    ->where(
                        'teacher_profile_id',
                        $lockedTeacher->id
                    )
                    ->whereNull('tuition_post_id')
                    ->whereIn(
                        'status',
                        ['pending', 'accepted']
                    )
                    ->lockForUpdate()
                    ->get();

                foreach ($legacyRequests as $legacyRequest) {
                    $legacyRequest->update([
                        'status' => 'cancelled',
                    ]);
                }

                /*

                |--------------------------------------------------------------------------

                | Duplicate Active Request Check Under Lock

                |--------------------------------------------------------------------------

                */



                $existingRequest = TeacherRequest::query()

                    ->where(

                        'student_user_id',

                        $student->id

                    )

                    ->where(

                        'teacher_profile_id',

                        $lockedTeacher->id

                    )

                    ->whereNotNull(

                        'tuition_post_id'

                    )

                    ->whereIn(

                        'status',

                        [

                            'pending',

                            'accepted',

                        ]

                    )

                    ->lockForUpdate()

                    ->first();



                if ($existingRequest) {

                    return [

                        'success' => false,

                        'message' =>

                            'You already have an active request with this teacher.',

                    ];

                }



                /*

                |--------------------------------------------------------------------------

                | Create Request

                |--------------------------------------------------------------------------

                */



                $teacherRequest =

                    TeacherRequest::create([

                        'student_user_id' =>

                            $student->id,



                        'teacher_profile_id' =>

                            $lockedTeacher->id,



                        'tuition_post_id' =>

                            $lockedTuition->id,



                        'message' =>

                            $validated['message'] ?? null,



                        'status' =>

                            'pending',

                    ]);



                return [

                    'success' => true,



                    'request_id' =>

                        $teacherRequest->id,

                ];

            },

            3

        );



        if (! $result['success']) {

            return back()->with(

                'error',

                $result['message']

            );

        }



        $teacherRequest = TeacherRequest::query()

            ->with([

                'teacherProfile.user',

                'student',

                'tuitionPost',

            ])

            ->find(

                $result['request_id']

            );



        if (

            $teacherRequest

                ?->teacherProfile

                ?->user

        ) {

            UserNotificationService::send(

                $teacherRequest

                    ->teacherProfile

                    ->user,



                'New Direct Tuition Request',



                auth()->user()->name

                    .' sent you a direct tuition request for '

                    .$teacherRequest->tuitionPost?->title

                    .'.',



                route(

                    'teacher.requests.index'

                ),



                'teacher_request'

            );

        }



        return redirect()

            ->route(

                'student.teacher-requests.index'

            )

            ->with(

                'success',

                'Teacher request sent successfully.'

            );

    }



    public function requests(): View

    {

        $requests = TeacherRequest::query()

            ->with([

                'teacherProfile.user',

                'teacherProfile.subjects',

                'tuitionPost',

            ])

            ->where(

                'student_user_id',

                auth()->id()

            )

            ->latest()

            ->paginate(15);



        return view(

            'student.teachers.requests',

            compact('requests')

        );

    }



    public function cancelRequest(

        TeacherRequest $teacherRequest

    ): RedirectResponse {

        $this->authorizeRequestOwnership(

            $teacherRequest

        );



        $result = DB::transaction(

            function () use ($teacherRequest) {

                $lockedRequest =

                    TeacherRequest::query()

                        ->with(

                            'teacherProfile.user'

                        )

                        ->lockForUpdate()

                        ->findOrFail(

                            $teacherRequest->id

                        );



                if (

                    $lockedRequest->student_user_id !==

                    auth()->id()

                ) {

                    abort(403);

                }



                if (

                    $lockedRequest->status !==

                    'pending'

                ) {

                    return [

                        'success' => false,

                        'message' =>

                            'Only pending requests can be cancelled.',

                    ];

                }



                $lockedRequest->update([

                    'status' =>

                        'cancelled',

                ]);



                return [

                    'success' => true,

                    'request_id' =>

                        $lockedRequest->id,

                ];

            },

            3

        );



        if (! $result['success']) {

            return back()->with(

                'error',

                $result['message']

            );

        }



        $teacherRequest =

            TeacherRequest::query()

                ->with(

                    'teacherProfile.user'

                )

                ->find(

                    $result['request_id']

                );



        if (

            $teacherRequest

                ?->teacherProfile

                ?->user

        ) {

            UserNotificationService::send(

                $teacherRequest

                    ->teacherProfile

                    ->user,



                'Teacher Request Cancelled',



                auth()->user()->name

                    .' cancelled the direct tuition request.',



                route(

                    'teacher.requests.index'

                ),



                'teacher_request'

            );

        }



        return back()->with(

            'success',

            'Teacher request cancelled successfully.'

        );

    }



    public function confirmTeacher(

        TeacherRequest $teacherRequest

    ): RedirectResponse {

        $this->authorizeRequestOwnership(

            $teacherRequest

        );



        $result = DB::transaction(

            function () use ($teacherRequest) {



                /*

                |--------------------------------------------------------------------------

                | Lock Student

                |--------------------------------------------------------------------------

                */



                User::query()

                    ->lockForUpdate()

                    ->findOrFail(

                        auth()->id()

                    );



                /*

                |--------------------------------------------------------------------------

                | Lock Request

                |--------------------------------------------------------------------------

                */



                $lockedRequest =

                    TeacherRequest::query()

                        ->with([

                            'teacherProfile.user',

                            'tuitionPost',

                        ])

                        ->lockForUpdate()

                        ->findOrFail(

                            $teacherRequest->id

                        );



                if (

                    $lockedRequest->student_user_id !==

                    auth()->id()

                ) {

                    abort(403);

                }



                if (

                    $lockedRequest->status !==

                    'accepted'

                ) {

                    return [

                        'success' => false,

                        'message' =>

                            'Only an accepted teacher request can be confirmed.',

                    ];

                }



                if ($lockedRequest->confirmed_at) {

                    return [

                        'success' => false,

                        'message' =>

                            'This teacher has already been confirmed.',

                    ];

                }



                /*

                |--------------------------------------------------------------------------

                | Lock Teacher

                |--------------------------------------------------------------------------

                */



                $lockedTeacher =

                    TeacherProfile::query()

                        ->with('user')

                        ->lockForUpdate()

                        ->findOrFail(

                            $lockedRequest

                                ->teacher_profile_id

                        );



                if (

                    ! $lockedTeacher->is_verified ||

                    ! $lockedTeacher->is_available ||

                    ! $lockedTeacher->user ||

                    $lockedTeacher->user->status !== 'active'

                ) {

                    return [

                        'success' => false,

                        'message' =>

                            'This teacher is no longer available or verified.',

                    ];

                }



                if (! $lockedRequest->tuition_post_id) {

                    return [

                        'success' => false,

                        'message' =>

                            'A linked tuition post is required before confirming this teacher.',

                    ];

                }



                /*

                |--------------------------------------------------------------------------

                | Lock Tuition

                |--------------------------------------------------------------------------

                */



                $lockedTuition =

                    TuitionPost::query()

                        ->lockForUpdate()

                        ->findOrFail(

                            $lockedRequest

                                ->tuition_post_id

                        );



                if (

                    $lockedTuition->user_id !==

                    auth()->id()

                ) {

                    abort(403);

                }



                if (

                    $lockedTuition->status !==

                    'published'

                ) {

                    return [

                        'success' => false,

                        'message' =>

                            'This tuition is no longer available for teacher confirmation.',

                    ];

                }



                /*

                |--------------------------------------------------------------------------

                | Final Assignment Protection

                |--------------------------------------------------------------------------

                */



                $existingAssignment =

                    TuitionAssignment::query()

                        ->where(

                            'tuition_post_id',

                            $lockedTuition->id

                        )

                        ->lockForUpdate()

                        ->first();



                if ($existingAssignment) {

                    return [

                        'success' => false,

                        'message' =>

                            'A final teacher assignment already exists for this tuition.',

                    ];

                }



                /*

                |--------------------------------------------------------------------------

                | Competing Applications

                |--------------------------------------------------------------------------

                */



                $competingApplications =

                    TuitionApplication::query()

                        ->where(

                            'tuition_post_id',

                            $lockedTuition->id

                        )

                        ->whereIn(

                            'status',

                            [

                                'pending',

                                'shortlisted',

                            ]

                        )

                        ->lockForUpdate()

                        ->get();



                /*

                |--------------------------------------------------------------------------

                | Other Direct Requests

                |--------------------------------------------------------------------------

                */



                $otherRequests =

                    TeacherRequest::query()

                        ->where(

                            'student_user_id',

                            auth()->id()

                        )

                        ->where(

                            'tuition_post_id',

                            $lockedTuition->id

                        )

                        ->where(

                            'id',

                            '!=',

                            $lockedRequest->id

                        )

                        ->whereIn(

                            'status',

                            [

                                'pending',

                                'accepted',

                            ]

                        )

                        ->lockForUpdate()

                        ->get();



                /*

                |--------------------------------------------------------------------------

                | Confirm Direct Request

                |--------------------------------------------------------------------------

                */



                $lockedRequest->update([

                    'confirmed_at' =>

                        now(),

                ]);



                /*

                |--------------------------------------------------------------------------

                | Create Final Assignment

                |--------------------------------------------------------------------------

                */



                $assignment =

                    TuitionAssignment::create([

                        'tuition_post_id' =>

                            $lockedTuition->id,



                        'teacher_profile_id' =>

                            $lockedTeacher->id,



                        'student_user_id' =>

                            $lockedTuition->user_id,



                        'source' =>

                            'direct_request',



                        'tuition_application_id' =>

                            null,



                        'teacher_request_id' =>

                            $lockedRequest->id,



                        'status' =>

                            'active',



                        'assigned_at' =>

                            now(),

                    ]);



                /*

                |--------------------------------------------------------------------------

                | Fill Tuition

                |--------------------------------------------------------------------------

                */



                $lockedTuition->update([

                    'status' =>

                        'filled',

                ]);



                /*

                |--------------------------------------------------------------------------

                | Reject Competing Applications

                |--------------------------------------------------------------------------

                */



                $rejectedApplicationIds = [];



                foreach (

                    $competingApplications

                    as $application

                ) {

                    $application->update([

                        'status' =>

                            'rejected',

                    ]);



                    $rejectedApplicationIds[] =

                        $application->id;

                }



                /*

                |--------------------------------------------------------------------------

                | Cancel Other Direct Requests

                |--------------------------------------------------------------------------

                */



                $cancelledRequestIds = [];



                foreach (

                    $otherRequests

                    as $otherRequest

                ) {

                    $otherRequest->update([

                        'status' =>

                            'cancelled',

                    ]);



                    $cancelledRequestIds[] =

                        $otherRequest->id;

                }



                return [

                    'success' => true,



                    'assignment_id' =>

                        $assignment->id,



                    'confirmed_request_id' =>

                        $lockedRequest->id,



                    'rejected_application_ids' =>

                        $rejectedApplicationIds,



                    'cancelled_request_ids' =>

                        $cancelledRequestIds,

                ];

            },

            3

        );



        if (! $result['success']) {

            return back()->with(

                'error',

                $result['message']

            );

        }



        /*

        |--------------------------------------------------------------------------

        | Confirmed Teacher Notification

        |--------------------------------------------------------------------------

        */



        $confirmedRequest =

            TeacherRequest::query()

                ->with([

                    'teacherProfile.user',

                    'tuitionPost',

                ])

                ->find(

                    $result[

                        'confirmed_request_id'

                    ]

                );



        if (

            $confirmedRequest

                ?->teacherProfile

                ?->user

        ) {

            UserNotificationService::send(

                $confirmedRequest

                    ->teacherProfile

                    ->user,



                'Tuition Assigned to You',



                'The student confirmed you for '

                    .$confirmedRequest

                        ->tuitionPost

                        ->title

                    .'.',



                route(

                    'teacher.assignments.index'

                ),



                'tuition_assignment'

            );

        }



        /*

        |--------------------------------------------------------------------------

        | Rejected Application Notifications

        |--------------------------------------------------------------------------

        */



        if (

            ! empty(

                $result[

                    'rejected_application_ids'

                ]

            )

        ) {

            $applications =

                TuitionApplication::query()

                    ->with([

                        'teacherProfile.user',

                        'tuitionPost',

                    ])

                    ->whereIn(

                        'id',

                        $result[

                            'rejected_application_ids'

                        ]

                    )

                    ->get();



            foreach (

                $applications

                as $application

            ) {

                $teacherUser =

                    $application

                        ->teacherProfile

                        ?->user;



                if (! $teacherUser) {

                    continue;

                }



                UserNotificationService::send(

                    $teacherUser,



                    'Application Status Updated',



                    'Another teacher was selected for '

                        .$application

                            ->tuitionPost

                            ->title

                        .'.',



                    route(

                        'teacher.tuitions.applications'

                    ),



                    'tuition_application'

                );

            }

        }



        /*

        |--------------------------------------------------------------------------

        | Cancelled Direct Request Notifications

        |--------------------------------------------------------------------------

        */



        if (

            ! empty(

                $result[

                    'cancelled_request_ids'

                ]

            )

        ) {

            $cancelledRequests =

                TeacherRequest::query()

                    ->with([

                        'teacherProfile.user',

                        'tuitionPost',

                    ])

                    ->whereIn(

                        'id',

                        $result[

                            'cancelled_request_ids'

                        ]

                    )

                    ->get();



            foreach (

                $cancelledRequests

                as $cancelledRequest

            ) {

                $teacherUser =

                    $cancelledRequest

                        ->teacherProfile

                        ?->user;



                if (! $teacherUser) {

                    continue;

                }



                UserNotificationService::send(

                    $teacherUser,



                    'Direct Request Closed',



                    'Another teacher was selected for '

                        .(

                            $cancelledRequest

                                ->tuitionPost

                                ?->title

                            ?? 'the tuition'

                        )

                        .'.',



                    route(

                        'teacher.requests.index'

                    ),



                    'teacher_request'

                );

            }

        }



        return redirect()

            ->route(

                'student.assignments.index'

            )

            ->with(

                'success',

                'Teacher confirmed and final tuition assignment created successfully.'

            );

    }



    private function authorizeRequestOwnership(

        TeacherRequest $teacherRequest

    ): void {

        abort_unless(

            $teacherRequest->student_user_id ===

            auth()->id(),

            403

        );

    }

}
