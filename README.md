Simple CLI for JIRA
===================
## Installation

As usual, first, `composer install`.

Then, If you have [Box](https://github.com/kherge/php-box) installed, awesome. Go to the root,
type `box build`, grab the jira.phar and do something interesting with it (I just throw it in
/usr/local/bin/jira).

If not, well, I guess you could set up an alias or something.

No idea if this'll work on Windows. It's at least possible.

## Usage

#### Time issues you're working on, and add a worklog directly to JIRA:

Start a timer on issue NEX-343:

    jira timer:start NEX-343

Stop that timer, and add a worklog on JIRA with message "Did some stuff", rounding the time to the nearest 15 minutes

    jira timer:stop NEX-343 -sm "Did some stuff"

To the nearest 30 minutes:

    jira timer:stop NEX-343 -sm "Did some stuff" -r 30

Same thing, but don't round:

    jira timer:stop NEX-343 -sdm "Did some stuff"

Same thing, but with the options expanded out

    jira timer:stop NEX-343 --send --noround --message="Did some stuff"

List all the timers you have, stopped or otherwise

    jira timer:list

You can stop the timer without actually logging the worklog, of course. You can always log it directly later
(parameters pretty much identical to timer:stop):

    jira timer:log NEX-343 --noround --message="Did some stuff"

Destroy the timer on NEX-343

    jira timer:kill NEX-343

**NOTE** Stopping a timer without sending the worklog immediately will only 'pause' the timer. Once stopped, a record is kept of the
length of your previously timed session; starting the timer again will start a new session, but the length of any previous sessions
will carry along until you finally log or kill it.

In other words, say you start working on NEX-343, and start a timer.

    jira timer:start NEX-343

Say one hour later you are interrupted to work on something else, so you stop the timer.

    jira timer:stop NEX-343

You'll see something similar to:

    60 minute(s) tracked.

Later on, you start the NEX-343 timer again, work for one hour, and stop the timer. Now, you'll see something like:

    120 minute(s) tracked.

But, be a little careful with this. Timers that exist across day boundaries aren't yet accounted for at all.
If you log time to JIRA from the timer, it will log the entire amount tracked for the timer in one lump amount,
*as of the most recent start time* for the timer. So, keep the timing to single days until I can be bothered
to fix that.

#### Just add worklogs directly without worrying about the timer:

Add a worklog to NEX-343 for today

    jira log:add NEX-343 "1h 45m" "Did some stuff"

Same deal, but on 2014-07-11 instead of today

    jira log:add NEX-343 "1h 45m" "Did some stuff" --date=2014-07-11

#### List worklogs on some issue:

List your worklogs on an issue

    jira log:list NEX-343

List all the worklogs on an issue, not just your own

    jira log:list NEX-343 --notjustme
   
#### Add / read comments on issues 

Visibility is set up in your config, and defaults to Developers. *Comments without a specific visibility are not yet implemented.*
 
List the comments on an issue 

    jira comment:list NEX-343

Add a comment to an issue with default visibility:

    jira comment:add NEX-343 "Comment body here"

Add a comment to an issue with some other visibility:

    jira comment:add NEX-343 "Comment body here" --visibility=role.SomeRole

Syntax here is somewhat important; the above corresponds to a visibility in the JSON of

    {
        /* other fields */
        "visibility": {
            "type": "role",
            "value": "SomeRole"
        }
    }

#### List issues according to your favorite filters defined in JIRA

List your favorite filters

    jira issue:find -l

List issues on the provided filter

    jira issue:find -f 12312

If you're weird and like JQL, you can use that directly

    jira issue:find --jql="assignee in (wcurtis)"

#### Deal with attachments

List the attachments on an issue.

    jira attach:list NEX-343

Attach a file to an issue. Filename is, obviously, the path to the file on your local machine.

    jira attach:put NEX-343 ~/my-file.jpg

Grab an attachment from JIRA. The `14523` here is an attachment id, likely retrieved in the previous
`jira attach:list NEX-343` command. The path here, maybe less obviously, is the *directory* you want to place the
file in. Leave the filename off, that'll get dealt with.

    jira attach:get 14523 ~/Downloads

For example, if that id pointed to an attachment with the filename my-file.jpg, the command above will download
and place the file in ~/Downloads/my-file.jpg.

If a file already exists at that location, an exception will be thrown; no overwriting if we can help it.

Also, I'm only about 90% sure this works as intended and won't blow up catastrophically if you type something
weird. So type carefully.

