window.ma = window.ma || {}
#$ ->
	#modalDelete = new ma.modals.ModalDelete 'button[data-delete]'
	#modalPage = new ma.modals.ModalPage 'ul.pager > li.counter a'
	#new ma.utils.DateField('input.form-control[data-date]')
	#new ma.utils.DateField('input.form-control[data-date]')

	#$('#modalCalendarPreview').modal('show')

	#calendar = new ma.utils.Calendar(2014, 8, 16)
	#calendar.generate '.calendar'
#	new ma.utils.Fullscreen('a#fullscreen')
	

window.ma = window.ma || {}

ma.knobs = {}
#
class ma.knobs.Knob
	constructor : (@trigger, @max, @value) ->
		@v
		@direction
		@i = 0
		#data-width="250" data-thickness=".1" data-fgcolor="#ff0000" data-linecap="round" data-bgcolor="#00FFFF" value="0" data-cursor=".1"
		$(@trigger)
			.val(0)
			.knob
				fgColor : "#428BCA"
				bgColor : "#ddd"
				stopper : false
				width : 250
				height : 250
				cursor : 1
				thickness : .1
				lineCap : 'round'
				max : 50
				change : @onChange
				release : @onRelease
				draw : @onDraw
				displayInput : false
		#console.log $(@trigger).knob().find('canvas')[0].getContext("2d").fillRect(0, 0, 150, 75)
		#console.log $(@trigger).knob().find('canvas')[0]
		$(@trigger).knob().addClass '.knob'
		$(@trigger).knob().prepend '<input class="display" type="text" value="0">'
		$(@trigger).knob().find('input').val(@value)
	onChange : (event) =>
		if @oldValue > event
			if @direction
				if (@value > 1) then @value--
				@direction = 0
			else
				@direction  = 1
		else if @oldValue < event
			if @direction == -1
				if (@value < @max) then @value++
				@direction = 0
			else
				@direction = -1
		@oldValue = event
		$(@trigger).knob().find('input').val(@value)
	onRelease : (event) =>
		$(this).trigger("release", { value : @value })

ma.modals = ma.modals || {}

class ma.modals.Modal
	constructor : (@trigger, @id = 'div#modal', @confirm = 'button[data-confirm]') ->
		$(@trigger).on click : @onTrigger
		$(@id).on 'hide.bs.modal' : @onDismiss
		$(@id).find(@confirm).on click : @onConfirm

	onTrigger : (event) =>
		event.preventDefault()
		$(@id).modal 'show'

	onDismiss : (event) =>

	onConfirm : (event) =>


class ma.modals.PopoverWorkout
	constructor : (@trigger) ->
		$(@trigger).on press : @onPress
		$(@trigger).on click : @onClick

	onPress : (event) =>
		event.preventDefault()

		target = $(event.currentTarget)

		button0 = '<button class="btn btn-default">0</button>'
		button50 = '<button class="btn btn-default">50</button>'
		button100 = '<button class="btn btn-default">100</button>'
		cancel= '<button class="btn btn-default">Cancel</button>'

		button = '<div style="width:200px;">'+button0+button50+button100+'</div>'

		target.popover({
			content : button
			html : true
			trigger : 'manual'
			placement : 'top'
		})

		$(@trigger).off('press')

		target.popover('show')
		event.stopPropagation()
		return false

	onClick : (event) ->
		event.preventDefault()





class ma.modals.ModalDelete extends ma.modals.Modal
	constructor : (@trigger, @id = '#modalDelete') ->
		@text = $(@id).find 'span[data-text]'
		#@href = $(@trigger).data('delete')
		@display = $(@text).find 'span[data-display]'
		super @trigger, @id

	onTrigger : (event) ->
		$(@display).text = "affe"
		super event

	onConfirm : (event) =>
		$(@trigger).parent().submit()

	onDismiss : (event) ->
		#@href = null
		super event

class ma.modals.ModalPage extends ma.modals.Modal
	constructor : (@trigger, @id = '#modalPage') ->
		$('#modelPage').modal('show')
		$(@id).find('button[data-first]').on click : @onFirst
		$(@id).find('button[data-last]').on click : @onLast

		@knob = new ma.knobs.Knob('#modalPage input[data-knob]', $(@trigger).data('max'), $(@trigger).data('value'))
		$(@knob).on release : @onRelease

		@display = $(@text).find 'span[data-display]'
		@display.text "affe"
		super @trigger, @id

	onRelease : (event, param) =>
		$(@id).modal 'hide'
		location.href = $(@trigger).attr('href')+':'+param.value

	onFirst : (event) =>
		$(@id).modal 'hide'
		location.href = $(@trigger).attr('href')+':1'

	onLast : (event) =>
		$(@id).modal 'hide'
		location.href = $(@trigger).attr('href')+':'+$(@trigger).data('max')

ma.modals = ma.modals || {}

class ma.modals.Program
	constructor : (@trigger, @modal) ->
		$(@trigger).on click : @onClick

		@url =
			edit : $(@modal).find('.scriptEdit').attr('href')
			view : $(@modal).find('.scriptView').attr('href')
			programChildren : $(@modal).find('.scriptProgramChildren').attr('href')
			start : $(@modal).find('.scriptStart').attr('href')
			race : $(@modal).find('.scriptRace').attr('href')

	onClick : (event) =>

		event.preventDefault()
		target = $(event.currentTarget)

		id = target.data('id')
		name = target.find('.scriptName').text()
		start = target.find('.scriptStart').data('date')
		race = target.find('.scriptRace').data('date')

		$(@modal).find('.scriptName').text(name)
		$(@modal).find('.scriptEdit').attr('href', @url.edit + '/' + id)
		$(@modal).find('.scriptView').attr('href', @url.view + '/' + id)
		$(@modal).find('.scriptProgramChildren').attr('href', @url.programChildren + '/' + id)
		$(@modal).find('.scriptStart').attr('href', @url.start + '/' + start)
		$(@modal).find('.scriptRace').attr('href', @url.race + '/' + race)

		$(@modal).modal('show')

ma.modals = ma.modals || {}

class ma.modals.ModalWorkout extends ma.modals.Modal
	constructor : (@trigger, @shapes, @id = '#modalWorkout') ->
		super @trigger, @id
		$(@id).find('.move button').on click : @onMove

		@shapeSlider = $(@id).find('.shape .shape-slider')
		@shapeCursor = $(@id).find('.shape .shape-cursor')
		@shapeLabel = $(@id).find('.shape .shape-label .label-body')
		@shapeSlider.find('a').on click : @onShape

		@completeFastBackward = $(@id).find('.complete-input .complete-fast-backward')
		@completeBackward = $(@id).find('.complete-input .complete-backward')
		@completeFastForward = $(@id).find('.complete-input .complete-fast-forward')
		@completeForward = $(@id).find('.complete-input .complete-forward')

		@completeFastBackward.on click : @onButtonStep
		@completeBackward.on click : @onButtonStep
		@completeFastForward.on click : @onButtonStep
		@completeForward.on click : @onButtonStep

		@completeSlider = $(@id).find('.complete .complete-slider')
		@completeCursor = @completeSlider.find('.complete-cursor')
		@completeLabel = $(@id).find('.complete .complete-label .label-body')
		@completeSlider.on mousedown : @onCompleteStart
		@completeSlider.on mousemove : @onCompleteMove
		@completeSlider.on mouseup : @onCompleteEnd
		@completeSlider.on mouseleave : @onCompleteEnd

		@completeInput = $(@id).find('#complete-input')
		@completeInput.on click : @onCompleteInput
		@completeInput.on focus : @onCompleteFocus
		@completeInput.on blur : @onCompleteBlur

		@completeStart = false
		#@canvas.get(0).addEventListener("touchmove", @onTouchMove, false)
		#@canvas.get(0).addEventListener("touchstart", @onTouchStart, false)
		#@canvas.get(0).addEventListener("touchend", @onTouchFinish, false)
		#@canvas.get(0).addEventListener("touchleave", @onTouchFinish, false)
		#@canvas.get(0).addEventListener("touchcancel", @onTouchFinish, false)

	onButtonStep : (event) =>
		event.preventDefault()
		currentTarget = $(event.currentTarget)
		if (!@updating)
			inputValue = parseInt(@completeInput.val())

			if (!currentTarget.hasClass('transition'))
				@completeCursor.addClass('transition')

			if (currentTarget.hasClass('complete-fast-backward'))
				inputValue = 0
			if (currentTarget.hasClass('complete-backward'))
				if (inputValue > 0)
					inputValue = inputValue - 10
					if (inputValue < 0)
						inputValue = 0
			if (currentTarget.hasClass('complete-forward'))
				if (inputValue < 100)
					inputValue = parseInt(inputValue) + 10
					if (inputValue > 100)
						inputValue = 100
			if (currentTarget.hasClass('complete-fast-forward'))
				inputValue = 100

			@updateAdjustments(true)
			$.ajax "#{ma.url}workouts/ajaxComplete",
				data :	{ 'complete' : inputValue, 'id' : @workoutId }
				success : (data, textStatus, jqXHR) =>
					@completeInput.val(parseInt(data['success'].complete) + '%')
					@completeCursor.css('width', parseInt(data['success'].complete) + '%')

					@updateAdjustments(false, data['success'])
				error : (jqXHR, textStatus, errorThrown) ->
					console.log jqXHR

	onCompleteInput : (event) =>
		event.preventDefault()
		@completeInput.select()

	onCompleteBlur : (event) =>
		event.preventDefault()

		currentTarget = $(event.currentTarget)
		inputValue = @completeInput.val()

		if (!currentTarget.hasClass('transition'))
			@completeCursor.addClass('transition')

		@completeInput.val(inputValue + '%')
		@completeCursor.css('width', inputValue + '%')

		if (!@updating)
			@updateAdjustments(true)
			$.ajax "#{ma.url}workouts/ajaxComplete",
				data :	{ 'complete' : inputValue, 'id' : @workoutId }
				success : (data, textStatus, jqXHR) =>
					@updateAdjustments(false, data['success'])
				error : (jqXHR, textStatus, errorThrown) ->
					console.log jqXHR

	onCompleteFocus : (event) =>
		event.preventDefault()

		currentTarget = $(event.currentTarget)
		@completeInput.val(parseInt(@completeInput.val()))

	onCompleteStart : (event) =>
		#@completeCursor.tooltip();
		@completeStart = true
		@completeCursor.removeClass('transition')
		@completeLabel.css("display","inline-block");
	onCompleteMove : (event) =>
		if (@completeStart)
			complete = @completeWidth(event.currentTarget.offsetWidth, event.offsetX)
			completeRounded = @completeWidth(event.currentTarget.offsetWidth, event.offsetX, true)
			@completeCursor.css('width', complete + '%')
			@completeInput.val(completeRounded + '%')
			@completeLabel.text( completeRounded + '% Complete')
	onCompleteEnd : (event) =>
		if (@completeStart)
			@completeStart = false
			complete = @completeWidth(event.currentTarget.offsetWidth, event.offsetX, true)
			@completeCursor.addClass('transition').css('width', complete + '%')
			@completeLabel.text(complete + '% Complete')
			@completeInput.val(complete + '%')
			setTimeout (=> @completeLabel.css('display','none')), 1000

			if (!@updating)
				@updateAdjustments(true)

				$.ajax "#{ma.url}workouts/ajaxComplete",
					data :	{ 'complete' : complete, 'id' : @workoutId }
					success : (data, textStatus, jqXHR) =>
						@updateAdjustments(false, data['success'])
					error : (jqXHR, textStatus, errorThrown) ->
						console.log jqXHR

	onShape : (event) =>
		event.preventDefault();
		if (!@updating)
			@updateAdjustments(true)
			@shapeLabel.css("display","inline-block");
			setTimeout (=> @shapeLabel.css('display','none')), 1000

			for shape, shapeString of @shapes
				if ($(event.currentTarget).hasClass(shapeString))
					@setShape(shape)

					$.ajax "#{ma.url}workouts/ajaxShape",
						data :	{ 'shape' : shape, 'id' : @workoutId }
						success : (data, textStatus, jqXHR) =>
							@updateAdjustments(false, data['success'])
						error : (jqXHR, textStatus, errorThrown) ->
							console.log jqXHR.responseText

	updateAdjustments : (update, data) =>
		@updating = update
		updateText = $(@id).find('.adjustment .updating')

		if (update)
			updateText.fadeIn('fast')
		else
			updateText.fadeOut('fast')
		if (data)
			$(@currentTrigger).find('.workout-footer .adjustment').addClass('show-adjust')
			complete = $(@currentTrigger).find('.workout-footer .adjustment .complete')
			shape = $(@currentTrigger).find('.workout-footer .adjustment .shape')
			if (data['complete'] || data['complete'] == 0)
				complete.text(data['complete'] + '% Complete')
				complete.data('complete', data['complete'])
			else
				complete.text('100' + '% Complete')
				complete.data('complete', '100')
			if (data['shape'] || data['shape'] == 0)
				shape.removeClass().addClass('shape ' + @shapes[data['shape']])
				shape.data('shape', data['shape'])
			else
				shape.removeClass().addClass('shape top-shape')
				shape.data('shape', 100)
			if (data['workouts']['adjustment'])
				for workout in data['workouts']['adjustment']
					if (workout['adjustment'])
						$('.workout[data-id=' + workout['id'] + ']').find('.workout-body .value').text(@format(workout))
			if (data['workouts']['scheda']['hide'])
				$('.workout').removeClass('scheda-hide')
				for workout in data['workouts']['scheda']['hide']
					console.log workout
					$('.workout[data-id=' + workout['Workout']['id'] + ']').addClass('scheda-hide')
			if (data['workouts']['scheda']['as'])
				console.log(data['workouts']['scheda'])
				for workout in data['workouts']['scheda']['as']
					$('.workout[data-id=' + workout['Workout']['id'] + ']').find('.workout-body .zone').text(workout['Zone']['name'])
		return @updating
	completeWidth : (width, x, ceil = false) ->
		percent = 100 * x / width
		if (ceil)
			percent = Math.round(percent / 5) * 5
		if(percent > 100)
			percent = 100
		else if (percent < 0)
			percent = 0
		return percent

	setShape : (shape) =>
		if (typeof @shapes[shape] != 'undefined')
			shape = @shapes[shape]
		else
			shape = 'top-shape'

		@shapeCursor.removeClass().addClass('shape-cursor').addClass(shape)

		$(@currentTrigger).find('.shape').removeClass().addClass('shape ' + shape)
		text = @shapeSlider.find('a.' + shape).text()
		@shapeLabel.text(text)
	setComplete : (complete) =>
		if (typeof complete != 'number')
			complete = 100

		@completeCursor.css('width', complete + '%')
		@completeInput.val(complete + '%')

	onTrigger : (event) =>
		@currentTrigger = event.currentTarget
		if ($(event.currentTarget).hasClass('workout-hidden'))
			return true
		else
			event.preventDefault()
			if ($(event.currentTarget).hasClass('workout-addrace'))
				@addrace = true
			else
				@addrace = false

		$(@id).modal 'show'
		if $(event.currentTarget).hasClass('workout-strength')
			$(@id).find('.strength-descriptions').show()
		else
			$(@id).find('.strength-descriptions').hide()

		@setShape($(@currentTrigger).find('.shape').data('shape'))
		@setComplete($(@currentTrigger).find('.complete').data('complete'))

		currentTrigger = $(event.currentTarget)

		@workoutId = currentTrigger.data('id')
		@sport = currentTrigger.data('sport')

		if (currentTrigger.hasClass('workout-addrace'))
			@addRace = true
		else
			@addRace = false

		value = $.trim(currentTrigger.find('.value').text())
		valueBrick = $.trim(currentTrigger.find('.value .body.brick').text())
		heartMin = $.trim(currentTrigger.find('.heart-rate .heart-min').text())
		heartMax = $.trim(currentTrigger.find('.heart-rate .heart-max').text())
		zone = $.trim(currentTrigger.find('.zone').text())
		zoneId = currentTrigger.data('zone')
		raceId = currentTrigger.data('race')
		trainingweek = currentTrigger.data('trainingweek')

		name = $.trim(currentTrigger.find('.workout-heading .name').text())

		tass = currentTrigger.data('tass')
		year = currentTrigger.data('year')
		month = currentTrigger.data('month')
		day = currentTrigger.data('day')

		month = if month.toString().length == 1 then '0' + month else month
		day = if day.toString().length == 1 then '0' + day else day

		if (@sport == 'strength')
			$(@id).find('.time, .brick, .distance').hide()
		else if (@sport == 'swim')
			$(@id).find('.time, .brick').hide()
			$(@id).find('.distance').show()
		else if (@sport == 'brick')
			$(@id).find('.row-time').addClass('row-brick')
			$(@id).find('.distance').hide()
			$(@id).find('.time, .brick').show()
		else
			$(@id).find('.row-time').removeClass('row-brick')
			$(@id).find('.distance, .brick').hide()
			$(@id).find('.time').show()


		$(@id).find('.move #moveYear').val(year)
		$(@id).find('.move #moveMonth').val(month)
		$(@id).find('.move #moveDay').val(day)

		$(@id).find('#distance, #time').val(value)
		$(@id).find('#brick').val(valueBrick)
		$(@id).find('#heart_min').val(heartMin)
		$(@id).find('#heart_max').val(heartMax)
		$(@id).find('.zone .body').text(zone)

		$(@id).find('.sport .name').text(name)
		$(@id).find('.sport .icon').html('<i class="ma ma-'+@sport+'"></i>')

		$(@id).removeClass('swim bike run strength brick').addClass(@sport)
		$(@id).find('.loading').slideDown()
		$(@id).find('.description .body').slideUp()

		data =
			zoneId : zoneId
			raceId : raceId
			trainingweek : trainingweek
			tass : tass
			sport : @sport
			value : parseInt(value)

		$.ajax "#{ma.url}descriptions/ajaxFind",
			data : data
			success : (data, textStatus, jqXHR) =>
				$(@id).find('.description .loading').slideUp("slow")
				$(@id).find('.description .error').hide()

				if (!data.error)

					descriptions = data.description.split(/\n/)

					descriptionHtml = '<ol>'
					for description in [0..descriptions.length-1]
						if descriptions[description] != ""
							descriptionHtml += '<li>' + descriptions[description] + '</li>'
					descriptionHtml += '</ol>'

					$(@id).find('.description .body').html(descriptionHtml).slideDown('slow')
				else
					$(@id).find('.description .body').html("").slideDown()
					$(@id).find('.description .error').slideDown('slow')

			error : (jqXHR, textStatus, errorThrown) ->
				console.log jqXHR

	onMove : (event) =>
		event.preventDefault()


		data =
			workoutId : @workoutId
			sport : @sport
			day : $(@id).find('.move #moveDay').val()
			month : $(@id).find('.move #moveMonth').val()
			year : $(@id).find('.move #moveYear').val()
			addRace : @addRace

		$.ajax "#{ma.url}workouts/ajaxMove",
			data : data
			success : (data, textStatus, jqXHR) =>
				if !data.error
					location.href = "#{ma.url}calendar/#{data.success.year}/#{data.success.month}"
				else
					console.log data
					location.reload()
			error : (jqXHR, textStatus, errorThrown) ->
				console.log jqXHR

	format : (workout) =>
    if (parseInt(workout['sport_id'], 10) == 1)
      return ' ' + workout['adjustment']
#    if (parseInt(workout['sport_id'] == 1)
#			return workout['adjustment']
    value = parseInt(workout['adjustment'], 10)
    minutes = Math.floor(value / 60)
    seconds = value - (minutes * 60)
    if (minutes < 10)
      minutes = "0" + minutes
    if (seconds < 10)
      seconds = "0" + seconds
    value = ' ' + minutes + ':' + seconds + ' '
    return value

ma.stripe = ma.stripe || {}

class ma.stripe.Validate
	constructor : (@trigger) ->
		$(@trigger).on('submit', @onSubmit)
		$(@trigger).find('button').prop('disabled', false)
	onSubmit : (event) =>
		event.preventDefault()

		$(@trigger).find('button').prop('disabled', true)
		Stripe.card.createToken($(@trigger), @onResponse)

		return false
	onResponse : (status, response) =>
		$(@trigger).find('.form-group.stripe').removeClass('has-error').find('.help-block').hide().text('')

		if response.error
			$(@trigger).find('.stripe.stripe-'+response.error.param).addClass('has-error').find('.help-block').show().text(response.error.message)
			$(@trigger).find('button').prop('disabled', false)
		else
			token = response.id
			$(@trigger).append($('<input type="hidden" name="data[Credit][token]" />').val(token))
			$(@trigger).get(0).submit()
			#console.log response

ma.utils = ma.utils || {}

ma.utils = ma.utils || {}
$.ajaxSetup
	type: 'post',
	dataType : 'json'
	

class ma.utils.Fullscreen
	constructor : (@trigger) ->
		$(@trigger).on click : @onTrigger
	onTrigger : (event)  =>

		event.preventDefault()

		$.ajax "#{ma.url}fullscreen",
			success : (data, textStatus, jqXHR) ->
				if data.content
					$('.fullscreen').removeClass('container-fluid').addClass('container')
					#if document.exitFullscreen
					#document.exitFullscreen()
					#else if document.mozCancelFullScreen
						#document.mozCancelFullScreen()
					#else if document.webkitExitFullscreen
						#document.webkitExitFullscreen()
				else
					$('.fullscreen').removeClass('container').addClass('container-fluid')
					#if document.body.requestFullScreen
						#document.body.requestFullScreen()
					#else if document.body.mozRequestFullScreen
						#document.body.mozRequestFullScreen()
					#else if document.body.webkitRequestFullScreen
						#document.boy.webkitRequestFullScreen()

			error : (jqXHR, textStatus, errorThrown) ->

class ma.utils.Step2
	constructor : () ->
		@oldValue

		@length = $('#ProgramTrainingLength')

		@race = {
			year : $('#ProgramRaceYear'),
			month : $('#ProgramRaceMonth'),
			day : $('#ProgramRaceDay')
		}

		@training = {
			year : $('#ProgramTrainingVisualYear'),
			month : $('#ProgramTrainingVisualMonth'),
			day : $('#ProgramTrainingVisualDay')
		}

		$('#ProgramTrainingVisual').show()

		@length.on input : @onTrigger

		@race.year.on change : @updateTrainingVisual
		@race.month.on change : @updateTrainingVisual
		@race.day.on change : @updateTrainingVisual

		@length.on blur : @onBlur

	onTrigger : (event) =>
		if !isNaN(parseInt(event.key) or event.key == "backspace")
			value = parseInt($(@length).val())
			if value > 20
				value = 20
			else if value < 1
				value = 1

			if value != @oldValue && !isNaN(value)
				@updateTrainingVisual()
			@oldValue = value

	onBlur : (event) =>
		if $(@trigger).val() > 20
			$(@trigger).val 20
		else if $(@trigger).val() <= 1
				$(@trigger).val 1

	updateTrainingVisual : (event) =>
		data = {
			data : {
				trainingLength : $(@length).val(),
				day : $('#ProgramRaceDay').val(),
				month : $('#ProgramRaceMonth').val(),
				year : $('#ProgramRaceYear').val()
			}
		}
		console.log data

		$.ajax "#{ma.url}programs/ajaxUpdateTraining",
			data : data
			success : (data, textStatus, jqXHR) =>
				console.log data
				if data.content.day.length == 1
					data.content.day = "0"+data.content.day
				if data.content.month.length == 1
					data.content.month = "0"+data.content.month
				@training.year.val data.content.year
				@training.month.val data.content.month
				@training.day.val data.content.day

			error : (jqXHR, textStatus, errorThrown) ->
				console.log 'error'

class ma.utils.Step4
	constructor : (@calendar, @modal) ->
		$('.programs.add').find('.workout').on('click', @onClick)
		@alert = $('<div class="alert alert-danger alert-dismissible" role="alert"/>').hide()
		@alert.append('<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>')
		@alert.append '<span class="text" />'


	onClick : (event) =>
		event.preventDefault()

		weekday = $(event.currentTarget).data('weekday')
		@key = $(event.currentTarget).data('key')

		console.log(weekday, 'weekday')

		$(@modal).find('.move').prop('disabled', false)
		$(@modal).find('.move[data-weekday='+weekday+']').prop('disabled', true)
		$(@modal).find('.move').on('click', @onButton)
		$(@modal).on('hide.bs.modal', @onHide)
		$(@modal).modal('show')

	onButton : (event) =>

		weekday = $(event.currentTarget).data('weekday')

		data =
		{
			key : @key
			weekday : weekday
		}

		$.ajax "#{ma.url}programs/ajaxMove",
			data : data
			success : (data, textStatus, jqXHR) =>
				if data.error
					$(@modal).modal('hide')
					setTimeout =>
						$('#flash').append(@alert)
						@alert.find('span.text').text(data.error)
						@alert.slideDown()
					,300
				else
					$(@modal).modal('hide')
					console.log data
					calendar  = '.programs.add .calendar.preview'

					workout = $('.workout[data-key='+@key+']')
					setTimeout =>
						workout.slideUp 'fast', =>
							workout.off().remove()
							workout.data('weekday', weekday)
							$(calendar).find('.calendar-cell.weekday-'+weekday).append(workout)
							workout.slideDown('fast').on('click', @onClick)
					,300

			error : (jqXHR, textStatus, errorThrown) ->
				console.log jqXHR
	onHide : (event) =>
		$(@modal).find('.move').off()

#class ma.utils.DateField
	#constructor : (@trigger) ->
		#replace = '<div class="calendar-group">
			#<input type="number" class="day form-control" placeholder="dd">
			#<input type="number" class="month form-control" placeholder="mm">
			#<input type="number" class="year form-control" placeholder="yyyy">
			#<button class="btn btn-default"><i class="fa fa-calendar"></i></button>
		#</div>'
		#$(@trigger).hide().parent().append(replace)
		##todo: foreach data-date element found

#class ma.utils.Calendar
	#constructor : (@year, @month, @day) ->
		#@days = ['Sun','Mon','Tue','Wed','Thur','Fri','Sat']
		#@date = new Date(@year, @month, @day)
		#console.log(@date.getDay())
		#console.log(_daysInMonth(@year, @month))

	#generate : (selector) ->
		##$(selector).remove('*')
		#_addHeader(selector, @days)
		#_addBody(selector, @days)

	#_addHeader = (selector, days) ->
		#dom = '<ul class="calendar-heading">
						#<li class="col-week">&nbsp;</li>
					#</ul>'

		#$(selector).append dom
		#for day in days
			#do (day) ->
				#$(selector).find('.calendar-heading').append '<li>'+day+'</li>'

	#_addBody = (selector, freg) ->
		#$(selector).append '<ul class="calendar-body"></ul>'
		
		#for row in [1..7]
			#do (row) ->
				#$(selector).find('ul.calendar-body').append ('<li class="calendar-row"> <h5>Week '+row+'</h5><ul class="calendar-col"></ul></li>')
			#for col in [0..6]
					#do (col) ->
						#$(selector).find('ul.calendar-body > li.calendar-row:last-child ul.calendar-col').append('<li class="calendar-cell"><div class="date"><span class="visible-xs-inline">'+freg[col]+',</span> '+(col+1)+'</div></li>')
	#_daysInMonth = (year, month) ->
		#new Date(year, month+1, 0).getDate()

ma.utils = ma.utils || {}

class ma.utils.ProgramChild
	constructor : (@raceType, @raceDistance, @date, @program, @trainingweek) ->
		@raceType = $(@raceType)
		@raceDistance = $(@raceDistance)
		@date = $(@date)
		@program = $(@program)
		@trainingweek = $(@trainingweek)

		@raceType.on change : @onChange
		@date.on change : @onDateChange
		
		#when user reloads page by posting check if fields needs to disable readonly prop
		@changeRaceDistance(@raceType.val())
		@changeTrainingweek()

	onChange : (event) =>
		value = $(event.currentTarget).val()
		@changeRaceDistance(value)

	changeRaceDistance : (value) =>
		if (value == 'swim' || value == 'run' || value == 'bike')
			@raceDistance.prop('readonly', false)
		else
			@raceDistance.prop('readonly', true)

	onDateChange : (event) =>
		#console.log @program.val()
		@changeTrainingweek()

	changeTrainingweek : () =>
		date = $('#ProgramChildDateYear').val() + '-' + $('#ProgramChildDateMonth').val() + '-' + $('#ProgramChildDateDay').val()
		$.ajax "#{ma.url}programChildren/ajaxGetTrainingweek",
			type: 'post',
			dataType : 'json'
			data :
				programId : @program.val()
				date : date
			success : @onSuccess
			error : @onError

	onSuccess : (data, textStatus, jqXHR) =>
		if !data.error
			console.log data
			@trainingweek.val(data.trainingweek)
		else
			console.log data
			@trainingweek.val('')

	onError : (jqXHR, textStatus, errorThrown) ->
		console.log 'error'
