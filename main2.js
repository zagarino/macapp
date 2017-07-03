ma.Fb = function() {
    this.$facebookLogin = $('#facebook-login');

    $facebookLogin.on('click', this.onClick);
};

ma.Fb.prototype.onClick = function(event) {
    event.preventDefault();
    console.log('yup');
}
;ma = ma || {};

$.ajaxSetup({
    type: 'post',
    dataType: 'json',
    error : function(jqXHR, textStatus, errorThrown) {
        console.log(jqXHR, textStatus, errorThrown);
    }
});

$(document).ready(function() {
});

ma.modalWorkout = function(domQuery, shapes)
{
    this.updating = false;
    this.shapes = shapes;
    this.$modal = $('#modalWorkout');
    this.$workout = null;
    this.$updating = this.$modal.find('.updating');
    $(domQuery).on('click', this, this.onClick);
    this.$modal.find('button.update-all').on('click', this, this.onUpdateAll);

}

ma.modalWorkout.prototype.onClick = function (event) {
    event.data.$workout = $(this);
};

ma.modalWorkout.prototype.onUpdateAll = function (event) {
    $shapeCursor = event.data.$modal.find('.shape-slider .shape-cursor');
    $completeCursor = event.data.$modal.find('.complete-slider .complete-cursor');

    var shape;
    var shapeValue;

    var completeValue = $completeCursor[0].style.width;

    if (!parseInt(completeValue)) {
        completevalue = 100;
    }

    for(shape in event.data.shapes) {
        if($shapeCursor.hasClass(event.data.shapes[shape])) {
            shapeValue = shape;
        }
    }

    if (!shapeValue) {
        shapeValue = 100;
    }

        $.ajax({
        url: ma.url + 'workouts/ajaxAll',
        data: {
        id: event.data.$workout.data('id'),
        shape: shapeValue,
        complete: completeValue,
        },
        success: function(data) {
            if(data.success) {
                //console.log(oldModalWorkout.updateAdjustments);
                event.data.updateAdjustments(false, data.success);
            }
        },
    });
};

ma.modalWorkout.prototype.updateAdjustments = function(update, data) {
  var complete, shape, updateText, workout;
  var context = this;
  this.updating = update;
  $updateText = $(this.$modal).find('.adjustment .updating');
  if (update) {
    $updateText.fadeIn('fast');
  } else {
    $updateText.fadeOut('fast');
  }

  if (data) {
    $(this.$workout).find('.workout-footer .adjustment').addClass('show-adjust');
    $complete = $(this.$workout).find('.workout-footer .adjustment .complete');
    $shape = $(this.$workout).find('.workout-footer .adjustment .shape');
    if (data.complete || data.complete === 0) {
      $complete.text(data.complete + '% Complete');
      $complete.data('complete', data.complete);
    } else {
      $complete.text('100' + '% Complete');
      $complete.data('complete', '100');
    }
    if (data.shape || data.shape === 0) {
      $shape.removeClass().addClass('shape ' + this.shapes[data.shape]);
      $shape.data('shape', data.shape);
    } else {
      $shape.removeClass().addClass('shape top-shape');
      $shape.data('shape', 100);
    }
    if (data.workouts.adjustment) {
        //console.log('adjustment', data.workouts.adjustment);
        data.workouts.adjustment.forEach(function(workout, index, workouts) {
            if (workout.adjustment) {
                $('.workout[data-id=' + workout.id + ']').find('.workout-body .value').text(context.format(workout));
            }
        });
    }

    if (data.workouts.scheda.hide) {
        $('.workout').removeClass('scheda-hide');
        //console.log('hide', data.workouts.scheda.hide);
        data.workouts.scheda.hide.forEach(function(workout, index, workouts) {
            $('.workout[data-id=' + workout.Workout.id + ']').addClass('scheda-hide');
        });
    }
    if (data['workouts']['scheda']['as']) {
      //console.log('as', data.workouts.scheda.as);
      data.workouts.scheda.as.forEach(function(workout, index, workouts) {
          $('.workout[data-id=' + workout.Workout.id + ']').find('.workout-body .zone').text(workout.Zone.name);
      });
    }
  }
  return this.updating;
};

ma.modalWorkout.prototype.format = function(workout) {
  var minutes, seconds, value;
  if (parseInt(workout.sport_id, 10) === 1) {
    return ' ' + workout.adjustment;
  }
  value = parseInt(workout.adjustment, 10);
  minutes = Math.floor(value / 60);
  seconds = value - (minutes * 60);
  if (minutes < 10) {
    minutes = '0' + minutes;
  }
  if (seconds < 10) {
    seconds = '0' + seconds;
  }
  value = ' ' + minutes + ':' + seconds + ' ';
  return value;
};
