/**
 * witySlider v1.2.10
 *
 * Copyright 2016, Creatiwity
 * https://creatiwity.net
 */

(function (factory) {
	if (typeof define === 'function' && define.amd) {
		// AMD. Register as an anonymous module.
		define(['jquery'], function($) {
			factory($, window, document);
		});
	} else {
		// Browser globals
		factory(jQuery, window, document);
	}
}(function ($, window, document, undefined) {

	// Create the defaults once
	var pluginName = "witySlider",
		defaults = {
			// General parameters
			mediaScales: {
				'xs': '(max-width: 768px)',
				'lg': '(min-width: 1200px)',
				'md': '(min-width: 992px)',
				'sm': true
			},
			fallbackMediaScale: 'md', // Old browsers without media-queries

			// Responsive parameters
			width: 1500, // Useless with fluid set to false
			height: 560,
			minHeight: 0, // Useless with fluid set to false
			resize: 'fixed', // Possible values : 'ratio', 'content', 'fixed', 'none'
			bullet_point_position: "top-left",
			controls: false,
			keyboard: true,
			rows: 1,
			cols: 1,
			speed: 500,
			pause: 3000,
			touch: true, // Use dragging to move slides
			auto: true,
			loop: true,
			specifics: {
				// 'sm': {width: 800} (example)
			}
		},
		hidden, visibilityChange, visibilityStatus = true;

	// Set the name of the hidden property and the change event for visibility
	if (typeof document.hidden !== "undefined") { // Opera 12.10 and Firefox 18 and later support
		hidden = "hidden";
		visibilityChange = "visibilitychange";
	} else if (typeof document.mozHidden !== "undefined") {
		hidden = "mozHidden";
		visibilityChange = "mozvisibilitychange";
	} else if (typeof document.msHidden !== "undefined") {
		hidden = "msHidden";
		visibilityChange = "msvisibilitychange";
	} else if (typeof document.webkitHidden !== "undefined") {
		hidden = "webkitHidden";
		visibilityChange = "webkitvisibilitychange";
	}

	function handleVisibilityChange() {
		visibilityStatus = !document[hidden];
	}

	// Warn if the browser doesn't support addEventListener or the Page Visibility API
	if (typeof document.addEventListener !== "undefined" &&
		typeof document[hidden] !== "undefined") {
		// Handle page visibility change
		document.addEventListener(visibilityChange, handleVisibilityChange, false);
	}

	Slider = (function () {

		function Slider(element, options) {
			var _commonOptions, _options, _contextualizedOptions,
				that = this;

			this.$element = $(element);

			this.options = function(newOpts) {
				var optsWithoutSpecifics;

				options = newOpts || options;

				if (!_options || newOpts) {
					_options = $.extend({}, defaults, options);
				}

				if (!_contextualizedOptions || newOpts) {
					_contextualizedOptions = {};
					optsWithoutSpecifics = $.extend({}, _options);
					delete optsWithoutSpecifics.specifics;

					$.each(_options.mediaScales, function(scale) {
						if (_options.specifics[scale]) {
							_contextualizedOptions[scale] = $.extend({}, optsWithoutSpecifics, _options.specifics[scale]);
						} else {
							_contextualizedOptions[scale] = optsWithoutSpecifics;
						}
					});
				}

				return _contextualizedOptions[this.getScale()];
			};

			this.getScale = function() {
				var result = _options.fallbackMediaScale;

				if (window && typeof window.matchMedia != 'undefined') {
					$.each(_options.mediaScales, function(scale, mediaQuery) {
						if (mediaQuery === true || window.matchMedia(mediaQuery).matches) {
							result = scale;
							return false;
						}
					});
				}

				return result;
			};

			this.ready = false;
			this.load();

			$(window).resize(function() {
				that.resize();
			});
		}

		Slider.prototype.resize = function() {
			var clientRect = this.$element[0].getBoundingClientRect(), // Fixes jQuery rounding bug
				globalWidth = clientRect.right - clientRect.left,
				globalHeight,
				itemWidth,
				itemHeight,
				options = this.options(),
				previousScale = this.currentScale;

			this.currentScale = this.getScale();

			itemWidth = globalWidth / options.cols;
			itemWidth -= (parseInt(this.$children.css('padding-left')) + parseInt(this.$children.css('padding-right')));

			if (options.fluid) {
				globalHeight = globalWidth / (options.width / options.height);
			} else if (options.contentHeight) {
				if (!previousScale || this.currentScale === previousScale) {
					this.$children.width(itemWidth);
				}
				globalHeight = this.$wrapper.find(".wslider-current").outerHeight(true);
			} else {
				globalHeight = options.height;
			}

			if ((options.contentHeight || options.fluid) && globalHeight < options.minHeight) {
				globalHeight = options.minHeight;
			}

			itemHeight = globalHeight / options.rows;

			if (previousScale && this.currentScale !== previousScale) {
				this.reload();
			} else {
				globalHeight -= (parseInt(this.$element.css('padding-top')) + parseInt(this.$element.css('padding-bottom')));

				this.$element.height(globalHeight);
				this.$children.width(itemWidth);

				if (!options.contentHeight) {
					this.$children.height(itemHeight);
				} else {
					this.$children.height('auto');
				}
			}
		};

		Slider.prototype.load = function(options) {
			var _options, i, len,
				row = 0, col = 0,
				$currentRow, $currentPage,
				that = this;

			this.pageCount = 0;

			if (options) {
				this.options(options);
			}

			_options = this.options();

			this.transitionSpeedString = (_options.speed / 1000) + "s";

			this.currentPage = 0;

			this.$element.addClass("wity-slider");

			if (_options.contentHeight) {
				this.$element.addClass('animate-height');
			} else {
				this.$element.removeClass('animate-height');
			}

			this.$children = this.$element.children();
			this.$children.detach();

			this.$wrapper = $('<div>').addClass('wrapper');
			this.$wrapper.appendTo(this.$element);

			this.$children.filter(':not(.inactive)').each(function(index, element) {
				var $wrapper = $('<div>'),
					newRow = (index === 0),
					newPage = (index === 0);

				if ((col === _options.cols && index !== 0)) {
					col = 0;
					row++;
					newRow = true;
				}

				if (row === _options.rows && index !== 0) {
					row = 0;
					that.pageCount++;
					newPage = true;
					newRow = true;
				}

				if (newPage) {
					$currentPage = $('<div>').addClass("wslider-page wslider-page-" + that.pageCount).attr("data-wity-slider-index", that.pageCount);
					$currentPage.appendTo(that.$wrapper);

					if (index > 0) {
						$currentPage.addClass("wslider-right");
					}
				}

				if (newRow) {
					if ($currentRow && $currentRow.length > 0) {
						$currentRow.append('<div class="clearfix"></div>');
					}

					$currentRow = $('<div>').addClass("wslider-row wslider-row-" + row);
					$currentRow.appendTo($currentPage);
				}

				if (index === 0) {
					$currentPage.addClass("wslider-current");
				}

				$wrapper.addClass("wslider-col wslider-col-" + col);
				$wrapper.appendTo($currentRow);
				$wrapper.append(element);

				col++;
			});

			this.resize();

			setTimeout(function() {
				that.ready = true;
				that.setAutoNext();
			}, 100);

			if (_options.controls && _options.controls !== "none" && this.pageCount > 0) {
				// Add left and right controls
				this.$left = $('<a>').attr("href", "#").addClass("wslider-controls-left");
				this.$right = $('<a>').attr("href", "#").addClass("wslider-controls-right");
				this.$controls = $('<div>').addClass("wslider-controls");

				this.$controls.append(this.$left);
				this.$controls.append(this.$right);

				this.$element.append(this.$controls);

				this.$left.click(function() {
					that.left();
					return false;
				});

				this.$right.click(function() {
					that.right();
					return false;
				});
			}

			if (_options.keyboard && this.pageCount > 0) {
				// On arrow press
				$(document).on('keydown', function(e) {
					if (that.isScrolledIntoView()) {
						switch (e.which) {
							case 37: // left
							that.left();
							return false;

							case 39: // right
							that.right();
							return false;
						}
					}
				});
			}

			if (_options.bullet_point_position && _options.bullet_point_position !== "none" && this.pageCount > 0) {
				this.$bullets = $("<ul>").addClass("nav-position nav-position-" + _options.bullet_point_position);

				for (i = 0; i <= this.pageCount; ++i) {
					var $slideIndicator = $('<li>').addClass("slide-indicator").attr("data-wity-slider-index", i);

					if (i === 0) {
						this.$pointer = $("<span>").addClass("pointer");
						$slideIndicator.append(this.$pointer);
					}

					this.$bullets.append($slideIndicator);
				}

				this.$bullets.appendTo(this.$element);

				this.pointerStep = this.$bullets.children().outerWidth(true);
				this.leftBorderWidth = parseInt(this.$bullets.children().css("border-left-width"), 10);

				this.$bullets.on("click", "li", function() {
					var $this = $(this),
						page = parseInt($this.attr("data-wity-slider-index"), 10);

					that.goTo(page);

					return false;
				});
			}

			if (_options.touch && this.pageCount > 0) {
				var lastDelta = 0,
					dragValue = 0,
					dragPercentage = 0,
					bindMove,
					unbindMove,
					onTouchMove,
					lastTime = 0,
					offsetValue = 5; // Constant

				bindMove = function(position, $element) {
					var firstPageX = position.pageX,
						firstPageY = position.pageY,
						canMove,
						lastPageX = firstPageX,
						maxDrag = $element[0].getBoundingClientRect().width, // Fixes jQuery rounding bug
						$prev = $element.prev(".wslider-page"),
						$next = $element.next(".wslider-page");

					that.moving = true;
					that.unsetAutoNext();

					$prev.removeClass('wslider-right').addClass('wslider-left');
					$next.addClass('wslider-right').removeClass('wslider-left');

					lastTime = (new Date()).getTime();

					onTouchMove = function(event) {
						var firstDX = 0,
							firstDY = 0;

						if (that.ready) {

							if (canMove === undefined) {
								firstDX = Math.abs(event.originalEvent.changedTouches[0].pageX - firstPageX);
								firstDY = Math.abs(event.originalEvent.changedTouches[0].pageY - firstPageY);

								if (firstDY > offsetValue || firstDX > offsetValue) {
									// Allow slider to move only if the first delta X is greater than the delta Y and greater than the offset
									canMove = firstDX > firstDY;
								}
							}

							if (canMove) {
								var currentPageX = event.originalEvent.changedTouches[0].pageX;

								dragValue =  currentPageX - firstPageX; // > 0 from right to left

								// max drag = $element.width()
								if (Math.abs(dragValue) > maxDrag) {
									if (dragValue < 0) {
										dragValue = -maxDrag;
									} else {
										dragValue = maxDrag;
									}
								}

								if (dragValue > 0 && $prev.length <= 0) {
									dragValue = 0;
								} else if (dragValue < 0 && $next.length <=0) {
									dragValue = 0;
								}

								lastDelta = currentPageX - lastPageX; // > 0 from right to left
								dragPercentage = dragValue / maxDrag * 100; // > 0 from right to left
								lastTime = (new Date()).getTime();

								lastPageX = currentPageX;

								$prev.css({
									'-webkit-transform': 'translate3d(' + (-100 + dragPercentage) + '%, 0, 0)',
									'-moz-transform': 'translate3d(' + (-100 + dragPercentage) + '%, 0, 0)',
									'-ms-transform': 'translate3d(' + (-100 + dragPercentage) + '%, 0, 0)',
									'-o-transform': 'translate3d(' + (-100 + dragPercentage) + '%, 0, 0)',
									'transform': 'translate3d(' + (-100 + dragPercentage) + '%, 0, 0)'
								});

								$element.css({
									'-webkit-transform': 'translate3d(' + dragPercentage + '%, 0, 0)',
									'-moz-transform': 'translate3d(' + dragPercentage + '%, 0, 0)',
									'-ms-transform': 'translate3d(' + dragPercentage + '%, 0, 0)',
									'-o-transform': 'translate3d(' + dragPercentage + '%, 0, 0)',
									'transform': 'translate3d(' + dragPercentage + '%, 0, 0)'
								});

								$next.css({
									'-webkit-transform': 'translate3d(' + (100 + dragPercentage) + '%, 0, 0)',
									'-moz-transform': 'translate3d(' + (100 + dragPercentage) + '%, 0, 0)',
									'-ms-transform': 'translate3d(' + (100 + dragPercentage) + '%, 0, 0)',
									'-o-transform': 'translate3d(' + (100 + dragPercentage) + '%, 0, 0)',
									'transform': 'translate3d(' + (100 + dragPercentage) + '%, 0, 0)'
								});

								return false;
							}
						}
					};

					$('body').on('touchmove', onTouchMove);
				};

				unbindMove = function() {
					var absDragValue = Math.abs(dragValue),
						$element = $('.wslider-current'),
						$prev = $element.prev(".wslider-page"),
						$next = $element.next(".wslider-page"),
						currentTime = (new Date()).getTime(),
						lastSpeed = lastDelta / (currentTime - lastTime),
						absLastSpeed = Math.abs(lastSpeed);

					if (that.moving) {
						$('body').off('touchmove', onTouchMove);

						if (absDragValue > 100) {
							if (dragValue > 0) {
								that.left();
							} else {
								that.right();
							}
						} else if (absLastSpeed > 0.25) {
							if (lastSpeed > 0) {
								that.left();
							} else {
								that.right();
							}
						} else {
							$element.addClass('wslider-animate');
							$prev.addClass('wslider-animate');
							$next.addClass('wslider-animate');

							setTimeout(function() {
								$element.removeAttr('style');
								$prev.removeAttr('style');
								$next.removeAttr('style');

								$element.removeClass('wslider-animate');
								$prev.removeClass('wslider-animate');
								$next.removeClass('wslider-animate');
							}, 50);
						}

						lastDelta = 0;
						dragValue = 0;
						dragPercentage = 0;

						that.moving = false;
						that.setAutoNext();
					}
				};

				this.$element.on('touchstart', '.wslider-current', function(event) {
					var $this = $(this);

					if (!that.moving) {
						bindMove(event.originalEvent.changedTouches[0], $this);
					}
				});

				$('body').on('touchend', unbindMove);
			}

			this.$element.trigger('ws.moved', [0, 0]);
		};

		Slider.prototype.left = function(index) {
			this.move('left', index);
		};

		Slider.prototype.right = function(index) {
			this.move('right', index);
		};

		Slider.prototype.move = function(direction, index) {
			if (this.ready && this.pageCount > 0) {
				var $current = this.$wrapper.find(".wslider-current"),
					$next = direction === 'left' ? $current.prev(".wslider-page") : $current.next(".wslider-page"),
					$other = direction !== 'left' ? $current.prev(".wslider-page") : $current.next(".wslider-page"),
					that = this,
					page;

				this.ready = false;
				this.unsetAutoNext();

				if (index !== undefined) {
					$next = this.$wrapper.children("[data-wity-slider-index='" + index+"']");
				} else if (!$next || $next.length <= 0) {
					if (!this.options().loop) {
						this.ready = true;
						return false;
					}

					$next = direction === 'left' ? this.$wrapper.children(".wslider-page").last() : this.$wrapper.children(".wslider-page").first();
					$next.removeAttr("style");
				}

				$other = $other.not($next);

				page = parseInt($next.attr("data-wity-slider-index"), 10);

				if (direction === 'left') {
					$next.removeClass("wslider-right");
					$next.addClass("wslider-left");
				} else {
					$next.removeClass("wslider-left");
					$next.addClass("wslider-right");
				}

				setTimeout(function() {
					$current.addClass("wslider-animate");
					$next.addClass("wslider-animate");

					$current.removeAttr("style");
					$next.removeAttr("style");
					$other.removeAttr("style");

					$current.css({
						"-webkit-transition-duration": that.transitionSpeedString,
						"-moz-transition-duration": that.transitionSpeedString,
						"-o-transition-duration": that.transitionSpeedString,
						"transition-duration": that.transitionSpeedString
					});

					$next.css({
						"-webkit-transition-duration": that.transitionSpeedString,
						"-moz-transition-duration": that.transitionSpeedString,
						"-o-transition-duration": that.transitionSpeedString,
						"transition-duration": that.transitionSpeedString
					});

					setTimeout(function() {
						$next.addClass("wslider-current");
						$current.removeClass("wslider-current");

						if (direction === 'left') {
							$next.removeClass("wslider-left");
							$current.addClass("wslider-right");
						} else {
							$next.removeClass("wslider-right");
							$current.addClass("wslider-left");
						}

						if (that.$pointer) {
							that.$pointer.css("margin-left", that.pointerStep * page - that.leftBorderWidth);
						}

						if (that.options().contentHeight) {
							that.$element.height($next.outerHeight(true));
						}

						setTimeout(function() {
							$current.removeClass("wslider-animate");
							$next.removeClass("wslider-animate");

							$current.css({
								"-webkit-transition-duration": "",
								"-moz-transition-duration": "",
								"-o-transition-duration": "",
								"transition-duration": ""
							});
							$next.css({
								"-webkit-transition-duration": "",
								"-moz-transition-duration": "",
								"-o-transition-duration": "",
								"transition-duration": ""
							});

							setTimeout(function() {
								$current.removeClass("wslider-left").addClass("wslider-right");
								$other.removeClass("wslider-left").addClass("wslider-right");
								that.ready = true;
								that.setAutoNext();

								that.$element.trigger('ws.moved', [parseInt($current.attr("data-wity-slider-index"), 10), page]);
							}, 20);
						}, that.options().speed);
					}, 20);
				}, 40);
			}
		};

		Slider.prototype.goTo = function(to) {
			var from = parseInt(this.$wrapper.find('.wslider-current').attr("data-wity-slider-index"), 10);

			if (from < to) {
				this.right(to);
			} else if (from > to) {
				this.left(to);
			}
		};

		Slider.prototype.setAutoNext = function() {
			var that = this;

			if (this.options().auto && this.pageCount > 0) {
				this.unsetAutoNext();

				this.autoNext = setTimeout(function() {
					if (visibilityStatus) {
						that.right();
					} else {
						that.setAutoNext();
					}
				}, this.options().pause);
			}
		};

		Slider.prototype.unsetAutoNext = function() {
			var that = this;

			if (this.autoNext) {
				clearTimeout(this.autoNext);
				this.autoNext = null;
			}
		};

		Slider.prototype.isScrolledIntoView = function() {
			var docViewTop = $(window).scrollTop(),
				docViewBottom = docViewTop + $(window).height(),

				elemTop = this.$element.offset().top,
				elemBottom = elemTop + this.$element.height();

			return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
		};

		Slider.prototype.reload = function(options) {
			this.destroy();
			this.load(options);
		};

		Slider.prototype.destroy = function() {
			this.ready = false;
			this.unsetAutoNext();
			$('body').off('touchstart');
			this.$element.children().remove();
			this.$element.append(this.$children);
			this.$element.removeClass("wity-slider");
		};

		return Slider;

	})();

	// A really lightweight plugin wrapper around the constructor,
	// preventing against multiple instantiations
	$.fn[pluginName] = function (options) {
		var args = Array.prototype.slice.call(arguments);

		this.each(function() {
			if (!$.data(this, "plugin_" + pluginName) ) {
				$.data(this, "plugin_" + pluginName, new Slider(this, typeof args[0] === 'string' ? {} : options));
			}

			if (typeof args[0] === 'string') {
				var method = args[0],
					slider = $.data(this, "plugin_" + pluginName);
				//remove the command name from the arguments
				args.splice(0, 1);

				slider[method].apply(slider, args);
			}
		});

		// chain jQuery functions
		return this;
	};

	$("[data-wity-slider]").each(function(index, element) {
		var $element = $(element),
			optionsStr = $element.attr("data-wity-slider"),
			options = {};

		if (optionsStr) {
			try {
				options = JSON.parse(optionsStr);
			} catch(e) {
				if (window.console) {
					console.warn("JSON parse error for slider with option string: " + optionsStr + ".");
				}
			}
		}

		$element.witySlider(options);
	});

}));
