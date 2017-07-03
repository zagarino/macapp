(function() {
  var __bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; },
    __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  window.ma = window.ma || {};

  window.ma = window.ma || {};

  ma.knobs = {};

  ma.knobs.Knob = (function() {
    function Knob(trigger, max, value) {
      this.trigger = trigger;
      this.max = max;
      this.value = value;
      this.onRelease = __bind(this.onRelease, this);
      this.onChange = __bind(this.onChange, this);
      this.v;
      this.direction;
      this.i = 0;
      $(this.trigger).val(0).knob({
        fgColor: "#428BCA",
        bgColor: "#ddd",
        stopper: false,
        width: 250,
        height: 250,
        cursor: 1,
        thickness: .1,
        lineCap: 'round',
        max: 50,
        change: this.onChange,
        release: this.onRelease,
        draw: this.onDraw,
        displayInput: false
      });
      $(this.trigger).knob().addClass('.knob');
      $(this.trigger).knob().prepend('<input class="display" type="text" value="0">');
      $(this.trigger).knob().find('input').val(this.value);
    }

    Knob.prototype.onChange = function(event) {
      if (this.oldValue > event) {
        if (this.direction) {
          if (this.value > 1) {
            this.value--;
          }
          this.direction = 0;
        } else {
          this.direction = 1;
        }
      } else if (this.oldValue < event) {
        if (this.direction === -1) {
          if (this.value < this.max) {
            this.value++;
          }
          this.direction = 0;
        } else {
          this.direction = -1;
        }
      }
      this.oldValue = event;
      return $(this.trigger).knob().find('input').val(this.value);
    };

    Knob.prototype.onRelease = function(event) {
      return $(this).trigger("release", {
        value: this.value
      });
    };

    return Knob;

  })();

  ma.modals = ma.modals || {};

  ma.modals.Modal = (function() {
    function Modal(trigger, id, confirm) {
      this.trigger = trigger;
      this.id = id != null ? id : 'div#modal';
      this.confirm = confirm != null ? confirm : 'button[data-confirm]';
      this.onConfirm = __bind(this.onConfirm, this);
      this.onDismiss = __bind(this.onDismiss, this);
      this.onTrigger = __bind(this.onTrigger, this);
      $(this.trigger).on({
        click: this.onTrigger
      });
      $(this.id).on({
        'hide.bs.modal': this.onDismiss
      });
      $(this.id).find(this.confirm).on({
        click: this.onConfirm
      });
    }

    Modal.prototype.onTrigger = function(event) {
      event.preventDefault();
      return $(this.id).modal('show');
    };

    Modal.prototype.onDismiss = function(event) {};

    Modal.prototype.onConfirm = function(event) {};

    return Modal;

  })();

  ma.modals.PopoverWorkout = (function() {
    function PopoverWorkout(trigger) {
      this.trigger = trigger;
      this.onPress = __bind(this.onPress, this);
      $(this.trigger).on({
        press: this.onPress
      });
      $(this.trigger).on({
        click: this.onClick
      });
    }

    PopoverWorkout.prototype.onPress = function(event) {
      var button, button0, button100, button50, cancel, target;
      event.preventDefault();
      target = $(event.currentTarget);
      button0 = '<button class="btn btn-default">0</button>';
      button50 = '<button class="btn btn-default">50</button>';
      button100 = '<button class="btn btn-default">100</button>';
      cancel = '<button class="btn btn-default">Cancel</button>';
      button = '<div style="width:200px;">' + button0 + button50 + button100 + '</div>';
      target.popover({
        content: button,
        html: true,
        trigger: 'manual',
        placement: 'top'
      });
      $(this.trigger).off('press');
      target.popover('show');
      event.stopPropagation();
      return false;
    };

    PopoverWorkout.prototype.onClick = function(event) {
      return event.preventDefault();
    };

    return PopoverWorkout;

  })();

  ma.modals.ModalDelete = (function(_super) {
    __extends(ModalDelete, _super);

    function ModalDelete(trigger, id) {
      this.trigger = trigger;
      this.id = id != null ? id : '#modalDelete';
      this.onConfirm = __bind(this.onConfirm, this);
      this.text = $(this.id).find('span[data-text]');
      this.display = $(this.text).find('span[data-display]');
      ModalDelete.__super__.constructor.call(this, this.trigger, this.id);
    }

    ModalDelete.prototype.onTrigger = function(event) {
      $(this.display).text = "affe";
      return ModalDelete.__super__.onTrigger.call(this, event);
    };

    ModalDelete.prototype.onConfirm = function(event) {
      return $(this.trigger).parent().submit();
    };

    ModalDelete.prototype.onDismiss = function(event) {
      return ModalDelete.__super__.onDismiss.call(this, event);
    };

    return ModalDelete;

  })(ma.modals.Modal);

  ma.modals.ModalPage = (function(_super) {
    __extends(ModalPage, _super);

    function ModalPage(trigger, id) {
      this.trigger = trigger;
      this.id = id != null ? id : '#modalPage';
      this.onLast = __bind(this.onLast, this);
      this.onFirst = __bind(this.onFirst, this);
      this.onRelease = __bind(this.onRelease, this);
      $('#modelPage').modal('show');
      $(this.id).find('button[data-first]').on({
        click: this.onFirst
      });
      $(this.id).find('button[data-last]').on({
        click: this.onLast
      });
      this.knob = new ma.knobs.Knob('#modalPage input[data-knob]', $(this.trigger).data('max'), $(this.trigger).data('value'));
      $(this.knob).on({
        release: this.onRelease
      });
      this.display = $(this.text).find('span[data-display]');
      this.display.text("affe");
      ModalPage.__super__.constructor.call(this, this.trigger, this.id);
    }

    ModalPage.prototype.onRelease = function(event, param) {
      $(this.id).modal('hide');
      return location.href = $(this.trigger).attr('href') + ':' + param.value;
    };

    ModalPage.prototype.onFirst = function(event) {
      $(this.id).modal('hide');
      return location.href = $(this.trigger).attr('href') + ':1';
    };

    ModalPage.prototype.onLast = function(event) {
      $(this.id).modal('hide');
      return location.href = $(this.trigger).attr('href') + ':' + $(this.trigger).data('max');
    };

    return ModalPage;

  })(ma.modals.Modal);

  ma.modals = ma.modals || {};

  ma.modals.Program = (function() {
    function Program(trigger, modal) {
      this.trigger = trigger;
      this.modal = modal;
      this.onClick = __bind(this.onClick, this);
      $(this.trigger).on({
        click: this.onClick
      });
      this.url = {
        edit: $(this.modal).find('.scriptEdit').attr('href'),
        view: $(this.modal).find('.scriptView').attr('href'),
        programChildren: $(this.modal).find('.scriptProgramChildren').attr('href'),
        start: $(this.modal).find('.scriptStart').attr('href'),
        race: $(this.modal).find('.scriptRace').attr('href')
      };
    }

    Program.prototype.onClick = function(event) {
      var id, name, race, start, target;
      event.preventDefault();
      target = $(event.currentTarget);
      id = target.data('id');
      name = target.find('.scriptName').text();
      start = target.find('.scriptStart').data('date');
      race = target.find('.scriptRace').data('date');
      $(this.modal).find('.scriptName').text(name);
      $(this.modal).find('.scriptEdit').attr('href', this.url.edit + '/' + id);
      $(this.modal).find('.scriptView').attr('href', this.url.view + '/' + id);
      $(this.modal).find('.scriptProgramChildren').attr('href', this.url.programChildren + '/' + id);
      $(this.modal).find('.scriptStart').attr('href', this.url.start + '/' + start);
      $(this.modal).find('.scriptRace').attr('href', this.url.race + '/' + race);
      return $(this.modal).modal('show');
    };

    return Program;

  })();

  ma.modals = ma.modals || {};

  ma.modals.ModalWorkout = (function(_super) {
    __extends(ModalWorkout, _super);

    function ModalWorkout(trigger, shapes, id) {
      this.trigger = trigger;
      this.shapes = shapes;
      this.id = id != null ? id : '#modalWorkout';
      this.format = __bind(this.format, this);
      this.onMove = __bind(this.onMove, this);
      this.onTrigger = __bind(this.onTrigger, this);
      this.setComplete = __bind(this.setComplete, this);
      this.setShape = __bind(this.setShape, this);
      this.updateAdjustments = __bind(this.updateAdjustments, this);
      this.onShape = __bind(this.onShape, this);
      this.onCompleteEnd = __bind(this.onCompleteEnd, this);
      this.onCompleteMove = __bind(this.onCompleteMove, this);
      this.onCompleteStart = __bind(this.onCompleteStart, this);
      this.onCompleteFocus = __bind(this.onCompleteFocus, this);
      this.onCompleteBlur = __bind(this.onCompleteBlur, this);
      this.onCompleteInput = __bind(this.onCompleteInput, this);
      this.onButtonStep = __bind(this.onButtonStep, this);
      ModalWorkout.__super__.constructor.call(this, this.trigger, this.id);
      $(this.id).find('.move button').on({
        click: this.onMove
      });
      this.shapeSlider = $(this.id).find('.shape .shape-slider');
      this.shapeCursor = $(this.id).find('.shape .shape-cursor');
      this.shapeLabel = $(this.id).find('.shape .shape-label .label-body');
      this.shapeSlider.find('a').on({
        click: this.onShape
      });
      this.completeFastBackward = $(this.id).find('.complete-input .complete-fast-backward');
      this.completeBackward = $(this.id).find('.complete-input .complete-backward');
      this.completeFastForward = $(this.id).find('.complete-input .complete-fast-forward');
      this.completeForward = $(this.id).find('.complete-input .complete-forward');
      this.completeFastBackward.on({
        click: this.onButtonStep
      });
      this.completeBackward.on({
        click: this.onButtonStep
      });
      this.completeFastForward.on({
        click: this.onButtonStep
      });
      this.completeForward.on({
        click: this.onButtonStep
      });
      this.completeSlider = $(this.id).find('.complete .complete-slider');
      this.completeCursor = this.completeSlider.find('.complete-cursor');
      this.completeLabel = $(this.id).find('.complete .complete-label .label-body');
      this.completeSlider.on({
        mousedown: this.onCompleteStart
      });
      this.completeSlider.on({
        mousemove: this.onCompleteMove
      });
      this.completeSlider.on({
        mouseup: this.onCompleteEnd
      });
      this.completeSlider.on({
        mouseleave: this.onCompleteEnd
      });
      this.completeInput = $(this.id).find('#complete-input');
      this.completeInput.on({
        click: this.onCompleteInput
      });
      this.completeInput.on({
        focus: this.onCompleteFocus
      });
      this.completeInput.on({
        blur: this.onCompleteBlur
      });
      this.completeStart = false;
    }

    ModalWorkout.prototype.onButtonStep = function(event) {
      var currentTarget, inputValue;
      event.preventDefault();
      currentTarget = $(event.currentTarget);
      if (!this.updating) {
        inputValue = parseInt(this.completeInput.val());
        if (!currentTarget.hasClass('transition')) {
          this.completeCursor.addClass('transition');
        }
        if (currentTarget.hasClass('complete-fast-backward')) {
          inputValue = 0;
        }
        if (currentTarget.hasClass('complete-backward')) {
          if (inputValue > 0) {
            inputValue = inputValue - 10;
            if (inputValue < 0) {
              inputValue = 0;
            }
          }
        }
        if (currentTarget.hasClass('complete-forward')) {
          if (inputValue < 100) {
            inputValue = parseInt(inputValue) + 10;
            if (inputValue > 100) {
              inputValue = 100;
            }
          }
        }
        if (currentTarget.hasClass('complete-fast-forward')) {
          inputValue = 100;
        }
        this.updateAdjustments(true);
        return $.ajax("" + ma.url + "workouts/ajaxComplete", {
          data: {
            'complete': inputValue,
            'id': this.workoutId
          },
          success: (function(_this) {
            return function(data, textStatus, jqXHR) {
              _this.completeInput.val(parseInt(data['success'].complete) + '%');
              _this.completeCursor.css('width', parseInt(data['success'].complete) + '%');
              return _this.updateAdjustments(false, data['success']);
            };
          })(this),
          error: function(jqXHR, textStatus, errorThrown) {
            return console.log(jqXHR);
          }
        });
      }
    };

    ModalWorkout.prototype.onCompleteInput = function(event) {
      event.preventDefault();
      return this.completeInput.select();
    };

    ModalWorkout.prototype.onCompleteBlur = function(event) {
      var currentTarget, inputValue;
      event.preventDefault();
      currentTarget = $(event.currentTarget);
      inputValue = this.completeInput.val();
      if (!currentTarget.hasClass('transition')) {
        this.completeCursor.addClass('transition');
      }
      this.completeInput.val(inputValue + '%');
      this.completeCursor.css('width', inputValue + '%');
      if (!this.updating) {
        this.updateAdjustments(true);
        return $.ajax("" + ma.url + "workouts/ajaxComplete", {
          data: {
            'complete': inputValue,
            'id': this.workoutId
          },
          success: (function(_this) {
            return function(data, textStatus, jqXHR) {
              return _this.updateAdjustments(false, data['success']);
            };
          })(this),
          error: function(jqXHR, textStatus, errorThrown) {
            return console.log(jqXHR);
          }
        });
      }
    };

    ModalWorkout.prototype.onCompleteFocus = function(event) {
      var currentTarget;
      event.preventDefault();
      currentTarget = $(event.currentTarget);
      return this.completeInput.val(parseInt(this.completeInput.val()));
    };

    ModalWorkout.prototype.onCompleteStart = function(event) {
      this.completeStart = true;
      this.completeCursor.removeClass('transition');
      return this.completeLabel.css("display", "inline-block");
    };

    ModalWorkout.prototype.onCompleteMove = function(event) {
      var complete, completeRounded;
      if (this.completeStart) {
        complete = this.completeWidth(event.currentTarget.offsetWidth, event.offsetX);
        completeRounded = this.completeWidth(event.currentTarget.offsetWidth, event.offsetX, true);
        this.completeCursor.css('width', complete + '%');
        this.completeInput.val(completeRounded + '%');
        return this.completeLabel.text(completeRounded + '% Complete');
      }
    };

    ModalWorkout.prototype.onCompleteEnd = function(event) {
      var complete;
      if (this.completeStart) {
        this.completeStart = false;
        complete = this.completeWidth(event.currentTarget.offsetWidth, event.offsetX, true);
        this.completeCursor.addClass('transition').css('width', complete + '%');
        this.completeLabel.text(complete + '% Complete');
        this.completeInput.val(complete + '%');
        setTimeout(((function(_this) {
          return function() {
            return _this.completeLabel.css('display', 'none');
          };
        })(this)), 1000);
        if (!this.updating) {
          this.updateAdjustments(true);
          return $.ajax("" + ma.url + "workouts/ajaxComplete", {
            data: {
              'complete': complete,
              'id': this.workoutId
            },
            success: (function(_this) {
              return function(data, textStatus, jqXHR) {
                return _this.updateAdjustments(false, data['success']);
              };
            })(this),
            error: function(jqXHR, textStatus, errorThrown) {
              return console.log(jqXHR);
            }
          });
        }
      }
    };

    ModalWorkout.prototype.onShape = function(event) {
      var shape, shapeString, _ref, _results;
      event.preventDefault();
      if (!this.updating) {
        this.updateAdjustments(true);
        this.shapeLabel.css("display", "inline-block");
        setTimeout(((function(_this) {
          return function() {
            return _this.shapeLabel.css('display', 'none');
          };
        })(this)), 1000);
        _ref = this.shapes;
        _results = [];
        for (shape in _ref) {
          shapeString = _ref[shape];
          if ($(event.currentTarget).hasClass(shapeString)) {
            this.setShape(shape);
            _results.push($.ajax("" + ma.url + "workouts/ajaxShape", {
              data: {
                'shape': shape,
                'id': this.workoutId
              },
              success: (function(_this) {
                return function(data, textStatus, jqXHR) {
                  return _this.updateAdjustments(false, data['success']);
                };
              })(this),
              error: function(jqXHR, textStatus, errorThrown) {
                return console.log(jqXHR.responseText);
              }
            }));
          } else {
            _results.push(void 0);
          }
        }
        return _results;
      }
    };

    ModalWorkout.prototype.updateAdjustments = function(update, data) {
      var complete, shape, updateText, workout, _i, _j, _k, _len, _len1, _len2, _ref, _ref1, _ref2;
      this.updating = update;
      updateText = $(this.id).find('.adjustment .updating');
      if (update) {
        updateText.fadeIn('fast');
      } else {
        updateText.fadeOut('fast');
      }
      if (data) {
        $(this.currentTrigger).find('.workout-footer .adjustment').addClass('show-adjust');
        complete = $(this.currentTrigger).find('.workout-footer .adjustment .complete');
        shape = $(this.currentTrigger).find('.workout-footer .adjustment .shape');
        if (data['complete'] || data['complete'] === 0) {
          complete.text(data['complete'] + '% Complete');
          complete.data('complete', data['complete']);
        } else {
          complete.text('100' + '% Complete');
          complete.data('complete', '100');
        }
        if (data['shape'] || data['shape'] === 0) {
          shape.removeClass().addClass('shape ' + this.shapes[data['shape']]);
          shape.data('shape', data['shape']);
        } else {
          shape.removeClass().addClass('shape top-shape');
          shape.data('shape', 100);
        }
        if (data['workouts']['adjustment']) {
          _ref = data['workouts']['adjustment'];
          for (_i = 0, _len = _ref.length; _i < _len; _i++) {
            workout = _ref[_i];
            if (workout['adjustment']) {
              $('.workout[data-id=' + workout['id'] + ']').find('.workout-body .value').text(this.format(workout));
            }
          }
        }
        if (data['workouts']['scheda']['hide']) {
          $('.workout').removeClass('scheda-hide');
          _ref1 = data['workouts']['scheda']['hide'];
          for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
            workout = _ref1[_j];
            console.log(workout);
            $('.workout[data-id=' + workout['Workout']['id'] + ']').addClass('scheda-hide');
          }
        }
        if (data['workouts']['scheda']['as']) {
          console.log(data['workouts']['scheda']);
          _ref2 = data['workouts']['scheda']['as'];
          for (_k = 0, _len2 = _ref2.length; _k < _len2; _k++) {
            workout = _ref2[_k];
            $('.workout[data-id=' + workout['Workout']['id'] + ']').find('.workout-body .zone').text(workout['Zone']['name']);
          }
        }
      }
      return this.updating;
    };

    ModalWorkout.prototype.completeWidth = function(width, x, ceil) {
      var percent;
      if (ceil == null) {
        ceil = false;
      }
      percent = 100 * x / width;
      if (ceil) {
        percent = Math.round(percent / 5) * 5;
      }
      if (percent > 100) {
        percent = 100;
      } else if (percent < 0) {
        percent = 0;
      }
      return percent;
    };

    ModalWorkout.prototype.setShape = function(shape) {
      var text;
      if (typeof this.shapes[shape] !== 'undefined') {
        shape = this.shapes[shape];
      } else {
        shape = 'top-shape';
      }
      this.shapeCursor.removeClass().addClass('shape-cursor').addClass(shape);
      $(this.currentTrigger).find('.shape').removeClass().addClass('shape ' + shape);
      text = this.shapeSlider.find('a.' + shape).text();
      return this.shapeLabel.text(text);
    };

    ModalWorkout.prototype.setComplete = function(complete) {
      if (typeof complete !== 'number') {
        complete = 100;
      }
      this.completeCursor.css('width', complete + '%');
      return this.completeInput.val(complete + '%');
    };

    ModalWorkout.prototype.onTrigger = function(event) {
      var currentTrigger, data, day, heartMax, heartMin, month, name, raceId, tass, trainingweek, value, valueBrick, year, zone, zoneId;
      this.currentTrigger = event.currentTarget;
      if ($(event.currentTarget).hasClass('workout-hidden')) {
        return true;
      } else {
        event.preventDefault();
        if ($(event.currentTarget).hasClass('workout-addrace')) {
          this.addrace = true;
        } else {
          this.addrace = false;
        }
      }
      $(this.id).modal('show');
      if ($(event.currentTarget).hasClass('workout-strength')) {
        $(this.id).find('.strength-descriptions').show();
      } else {
        $(this.id).find('.strength-descriptions').hide();
      }
      this.setShape($(this.currentTrigger).find('.shape').data('shape'));
      this.setComplete($(this.currentTrigger).find('.complete').data('complete'));
      currentTrigger = $(event.currentTarget);
      this.workoutId = currentTrigger.data('id');
      this.sport = currentTrigger.data('sport');
      if (currentTrigger.hasClass('workout-addrace')) {
        this.addRace = true;
      } else {
        this.addRace = false;
      }
      value = $.trim(currentTrigger.find('.value').text());
      valueBrick = $.trim(currentTrigger.find('.value .body.brick').text());
      heartMin = $.trim(currentTrigger.find('.heart-rate .heart-min').text());
      heartMax = $.trim(currentTrigger.find('.heart-rate .heart-max').text());
      zone = $.trim(currentTrigger.find('.zone').text());
      zoneId = currentTrigger.data('zone');
      raceId = currentTrigger.data('race');
      trainingweek = currentTrigger.data('trainingweek');
      name = $.trim(currentTrigger.find('.workout-heading .name').text());
      tass = currentTrigger.data('tass');
      year = currentTrigger.data('year');
      month = currentTrigger.data('month');
      day = currentTrigger.data('day');
      month = month.toString().length === 1 ? '0' + month : month;
      day = day.toString().length === 1 ? '0' + day : day;
      if (this.sport === 'strength') {
        $(this.id).find('.time, .brick, .distance').hide();
      } else if (this.sport === 'swim') {
        $(this.id).find('.time, .brick').hide();
        $(this.id).find('.distance').show();
      } else if (this.sport === 'brick') {
        $(this.id).find('.row-time').addClass('row-brick');
        $(this.id).find('.distance').hide();
        $(this.id).find('.time, .brick').show();
      } else {
        $(this.id).find('.row-time').removeClass('row-brick');
        $(this.id).find('.distance, .brick').hide();
        $(this.id).find('.time').show();
      }
      $(this.id).find('.move #moveYear').val(year);
      $(this.id).find('.move #moveMonth').val(month);
      $(this.id).find('.move #moveDay').val(day);
      $(this.id).find('#distance, #time').val(value);
      $(this.id).find('#brick').val(valueBrick);
      $(this.id).find('#heart_min').val(heartMin);
      $(this.id).find('#heart_max').val(heartMax);
      $(this.id).find('.zone .body').text(zone);
      $(this.id).find('.sport .name').text(name);
      $(this.id).find('.sport .icon').html('<i class="ma ma-' + this.sport + '"></i>');
      $(this.id).removeClass('swim bike run strength brick').addClass(this.sport);
      $(this.id).find('.loading').slideDown();
      $(this.id).find('.description .body').slideUp();
      data = {
        workoutId: this.workoutId,
        zoneId: zoneId,
        raceId: raceId,
        addrace: this.addrace,
        trainingweek: trainingweek,
        tass: tass,
        sport: this.sport,
        value: parseInt(value)
      };
      console.log(data);
      return $.ajax("" + ma.url + "descriptions/ajaxFind", {
        data: data,
        success: (function(_this) {
          return function(data, textStatus, jqXHR) {
            var description, descriptionHtml, descriptions, _i, _ref;
            $(_this.id).find('.description .loading').slideUp("slow");
            $(_this.id).find('.description .error').hide();
            if (!data.error) {
              descriptions = data.description.split(/\n/);
              descriptionHtml = '<ol>';
              for (description = _i = 0, _ref = descriptions.length - 1; 0 <= _ref ? _i <= _ref : _i >= _ref; description = 0 <= _ref ? ++_i : --_i) {
                if (descriptions[description] !== "") {
                  descriptionHtml += '<li>' + descriptions[description] + '</li>';
                }
              }
              descriptionHtml += '</ol>';
              return $(_this.id).find('.description .body').html(descriptionHtml).slideDown('slow');
            } else {
              $(_this.id).find('.description .body').html("").slideDown();
              return $(_this.id).find('.description .error').slideDown('slow');
            }
          };
        })(this),
        error: function(jqXHR, textStatus, errorThrown) {
          return console.log(jqXHR);
        }
      });
    };

    ModalWorkout.prototype.onMove = function(event) {
      var data;
      event.preventDefault();
      data = {
        workoutId: this.workoutId,
        sport: this.sport,
        day: $(this.id).find('.move #moveDay').val(),
        month: $(this.id).find('.move #moveMonth').val(),
        year: $(this.id).find('.move #moveYear').val(),
        addRace: this.addRace
      };
      return $.ajax("" + ma.url + "workouts/ajaxMove", {
        data: data,
        success: (function(_this) {
          return function(data, textStatus, jqXHR) {
            if (!data.error) {
              return location.href = "" + ma.url + "calendar/" + data.success.year + "/" + data.success.month;
            } else {
              console.log(data);
              return location.reload();
            }
          };
        })(this),
        error: function(jqXHR, textStatus, errorThrown) {
          return console.log(jqXHR);
        }
      });
    };

    ModalWorkout.prototype.format = function(workout) {
      var minutes, seconds, value;
      if (parseInt(workout['sport_id'], 10) === 1) {
        return ' ' + workout['adjustment'];
      }
      value = parseInt(workout['adjustment'], 10);
      minutes = Math.floor(value / 60);
      seconds = value - (minutes * 60);
      if (minutes < 10) {
        minutes = "0" + minutes;
      }
      if (seconds < 10) {
        seconds = "0" + seconds;
      }
      value = ' ' + minutes + ':' + seconds + ' ';
      return value;
    };

    return ModalWorkout;

  })(ma.modals.Modal);

  ma.stripe = ma.stripe || {};

  ma.stripe.Validate = (function() {
    function Validate(trigger) {
      this.trigger = trigger;
      this.onResponse = __bind(this.onResponse, this);
      this.onSubmit = __bind(this.onSubmit, this);
      $(this.trigger).on('submit', this.onSubmit);
      $(this.trigger).find('button').prop('disabled', false);
    }

    Validate.prototype.onSubmit = function(event) {
      event.preventDefault();
      $(this.trigger).find('button').prop('disabled', true);
      Stripe.card.createToken($(this.trigger), this.onResponse);
      return false;
    };

    Validate.prototype.onResponse = function(status, response) {
      var token;
      $(this.trigger).find('.form-group.stripe').removeClass('has-error').find('.help-block').hide().text('');
      if (response.error) {
        $(this.trigger).find('.stripe.stripe-' + response.error.param).addClass('has-error').find('.help-block').show().text(response.error.message);
        return $(this.trigger).find('button').prop('disabled', false);
      } else {
        token = response.id;
        $(this.trigger).append($('<input type="hidden" name="data[Credit][token]" />').val(token));
        return $(this.trigger).get(0).submit();
      }
    };

    return Validate;

  })();

  ma.utils = ma.utils || {};

  ma.utils = ma.utils || {};

  $.ajaxSetup({
    type: 'post',
    dataType: 'json'
  });

  ma.utils.Fullscreen = (function() {
    function Fullscreen(trigger) {
      this.trigger = trigger;
      this.onTrigger = __bind(this.onTrigger, this);
      $(this.trigger).on({
        click: this.onTrigger
      });
    }

    Fullscreen.prototype.onTrigger = function(event) {
      event.preventDefault();
      return $.ajax("" + ma.url + "fullscreen", {
        success: function(data, textStatus, jqXHR) {
          if (data.content) {
            return $('.fullscreen').removeClass('container-fluid').addClass('container');
          } else {
            return $('.fullscreen').removeClass('container').addClass('container-fluid');
          }
        },
        error: function(jqXHR, textStatus, errorThrown) {}
      });
    };

    return Fullscreen;

  })();

  ma.utils.Step2 = (function() {
    function Step2() {
      this.updateTrainingVisual = __bind(this.updateTrainingVisual, this);
      this.onBlur = __bind(this.onBlur, this);
      this.onTrigger = __bind(this.onTrigger, this);
      this.oldValue;
      this.length = $('#ProgramTrainingLength');
      this.race = {
        year: $('#ProgramRaceYear'),
        month: $('#ProgramRaceMonth'),
        day: $('#ProgramRaceDay')
      };
      this.training = {
        year: $('#ProgramTrainingVisualYear'),
        month: $('#ProgramTrainingVisualMonth'),
        day: $('#ProgramTrainingVisualDay')
      };
      $('#ProgramTrainingVisual').show();
      this.length.on({
        input: this.onTrigger
      });
      this.race.year.on({
        change: this.updateTrainingVisual
      });
      this.race.month.on({
        change: this.updateTrainingVisual
      });
      this.race.day.on({
        change: this.updateTrainingVisual
      });
      this.length.on({
        blur: this.onBlur
      });
    }

    Step2.prototype.onTrigger = function(event) {
      var value;
      if (!isNaN(parseInt(event.key) || event.key === "backspace")) {
        value = parseInt($(this.length).val());
        if (value > 20) {
          value = 20;
        } else if (value < 1) {
          value = 1;
        }
        if (value !== this.oldValue && !isNaN(value)) {
          this.updateTrainingVisual();
        }
        return this.oldValue = value;
      }
    };

    Step2.prototype.onBlur = function(event) {
      if ($(this.trigger).val() > 20) {
        return $(this.trigger).val(20);
      } else if ($(this.trigger).val() <= 1) {
        return $(this.trigger).val(1);
      }
    };

    Step2.prototype.updateTrainingVisual = function(event) {
      var data;
      data = {
        data: {
          trainingLength: $(this.length).val(),
          day: $('#ProgramRaceDay').val(),
          month: $('#ProgramRaceMonth').val(),
          year: $('#ProgramRaceYear').val()
        }
      };
      console.log(data);
      return $.ajax("" + ma.url + "programs/ajaxUpdateTraining", {
        data: data,
        success: (function(_this) {
          return function(data, textStatus, jqXHR) {
            console.log(data);
            if (data.content.day.length === 1) {
              data.content.day = "0" + data.content.day;
            }
            if (data.content.month.length === 1) {
              data.content.month = "0" + data.content.month;
            }
            _this.training.year.val(data.content.year);
            _this.training.month.val(data.content.month);
            return _this.training.day.val(data.content.day);
          };
        })(this),
        error: function(jqXHR, textStatus, errorThrown) {
          return console.log('error');
        }
      });
    };

    return Step2;

  })();

  ma.utils.Step4 = (function() {
    function Step4(calendar, modal) {
      this.calendar = calendar;
      this.modal = modal;
      this.onHide = __bind(this.onHide, this);
      this.onButton = __bind(this.onButton, this);
      this.onClick = __bind(this.onClick, this);
      $('.programs.add').find('.workout').on('click', this.onClick);
      this.alert = $('<div class="alert alert-danger alert-dismissible" role="alert"/>').hide();
      this.alert.append('<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>');
      this.alert.append('<span class="text" />');
    }

    Step4.prototype.onClick = function(event) {
      var weekday;
      event.preventDefault();
      weekday = $(event.currentTarget).data('weekday');
      this.key = $(event.currentTarget).data('key');
      console.log(weekday, 'weekday');
      $(this.modal).find('.move').prop('disabled', false);
      $(this.modal).find('.move[data-weekday=' + weekday + ']').prop('disabled', true);
      $(this.modal).find('.move').on('click', this.onButton);
      $(this.modal).on('hide.bs.modal', this.onHide);
      return $(this.modal).modal('show');
    };

    Step4.prototype.onButton = function(event) {
      var data, weekday;
      weekday = $(event.currentTarget).data('weekday');
      data = {
        key: this.key,
        weekday: weekday
      };
      return $.ajax("" + ma.url + "programs/ajaxMove", {
        data: data,
        success: (function(_this) {
          return function(data, textStatus, jqXHR) {
            var calendar, workout;
            if (data.error) {
              $(_this.modal).modal('hide');
              return setTimeout(function() {
                $('#flash').append(_this.alert);
                _this.alert.find('span.text').text(data.error);
                return _this.alert.slideDown();
              }, 300);
            } else {
              $(_this.modal).modal('hide');
              console.log(data);
              calendar = '.programs.add .calendar.preview';
              workout = $('.workout[data-key=' + _this.key + ']');
              return setTimeout(function() {
                return workout.slideUp('fast', function() {
                  workout.off().remove();
                  workout.data('weekday', weekday);
                  $(calendar).find('.calendar-cell.weekday-' + weekday).append(workout);
                  return workout.slideDown('fast').on('click', _this.onClick);
                });
              }, 300);
            }
          };
        })(this),
        error: function(jqXHR, textStatus, errorThrown) {
          return console.log(jqXHR);
        }
      });
    };

    Step4.prototype.onHide = function(event) {
      return $(this.modal).find('.move').off();
    };

    return Step4;

  })();

  ma.utils = ma.utils || {};

  ma.utils.ProgramChild = (function() {
    function ProgramChild(raceType, raceDistance, date, program, trainingweek) {
      this.raceType = raceType;
      this.raceDistance = raceDistance;
      this.date = date;
      this.program = program;
      this.trainingweek = trainingweek;
      this.onSuccess = __bind(this.onSuccess, this);
      this.changeTrainingweek = __bind(this.changeTrainingweek, this);
      this.onDateChange = __bind(this.onDateChange, this);
      this.changeRaceDistance = __bind(this.changeRaceDistance, this);
      this.onChange = __bind(this.onChange, this);
      this.raceType = $(this.raceType);
      this.raceDistance = $(this.raceDistance);
      this.date = $(this.date);
      this.program = $(this.program);
      this.trainingweek = $(this.trainingweek);
      this.raceType.on({
        change: this.onChange
      });
      this.date.on({
        change: this.onDateChange
      });
      this.changeRaceDistance(this.raceType.val());
      this.changeTrainingweek();
    }

    ProgramChild.prototype.onChange = function(event) {
      var value;
      value = $(event.currentTarget).val();
      return this.changeRaceDistance(value);
    };

    ProgramChild.prototype.changeRaceDistance = function(value) {
      if (value === 'swim' || value === 'run' || value === 'bike') {
        return this.raceDistance.prop('readonly', false);
      } else {
        return this.raceDistance.prop('readonly', true);
      }
    };

    ProgramChild.prototype.onDateChange = function(event) {
      return this.changeTrainingweek();
    };

    ProgramChild.prototype.changeTrainingweek = function() {
      var date;
      date = $('#ProgramChildDateYear').val() + '-' + $('#ProgramChildDateMonth').val() + '-' + $('#ProgramChildDateDay').val();
      return $.ajax("" + ma.url + "programChildren/ajaxGetTrainingweek", {
        type: 'post',
        dataType: 'json',
        data: {
          programId: this.program.val(),
          date: date
        },
        success: this.onSuccess,
        error: this.onError
      });
    };

    ProgramChild.prototype.onSuccess = function(data, textStatus, jqXHR) {
      if (!data.error) {
        console.log(data);
        return this.trainingweek.val(data.trainingweek);
      } else {
        console.log(data);
        return this.trainingweek.val('');
      }
    };

    ProgramChild.prototype.onError = function(jqXHR, textStatus, errorThrown) {
      return console.log('error');
    };

    return ProgramChild;

  })();

}).call(this);

//# sourceMappingURL=main.js.map
