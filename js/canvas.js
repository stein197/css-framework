var Path = {
	canvas: null,
	dimens: null,

	init: function(){
		if(!this.canvas)
			this.setCanvas();
	},

	setCanvas: function(){
		var canvas = document.getElementById("canvas");
		Path.canvas = canvas.getContext("2d");
		Path.dimens = {
			width: canvas.width,
			height: canvas.height
		};
	},
	clear: function(){
		Path.canvas.clearRect(0, 0, Path.dimens.width, Path.dimens.height);
		Path.canvas.beginPath();
	},
	animateQ: function(p1, p2, p3, q, duration){
		Path.clear();
		Path.canvas.moveTo(p1.x, p1.y);
		var stepSize = duration / q;
		var curTime = 0;
		var prevX = p1.x;
		var prevY = p1.y;
		var x, y, t;
		for(var i = 0; i <= q; i++){
			t = i / q;
			x = (1 - t) ** 2 * p1.x + 2 * t * (1 - t) * p2.x + t ** 2 * p3.x;
			y = (1 - t) ** 2 * p1.y + 2 * t * (1 - t) * p2.y + t ** 2 * p3.y;
			color = Math.round(0xFF * t).toString(16);
			color = "#" + color + color + color;
			Path.lineTo(x, y, curTime += stepSize, prevX, prevY, color);
			prevX = x;
			prevY = y;
		}
	},
	animateC: function(p1, p2, p3, p4, q, duration){
		Path.clear();
		Path.canvas.moveTo(p1.x, p1.y);
		var stepSize = duration / q;
		var curTime = 0;
		var prevX = p1.x;
		var prevY = p1.y;
		var x, y, t;
		for(var i = 0; i <= q; i++){
			t = i / q;
			x = (1 - t) ** 3 * p1.x + 3 * t * (1 - t) ** 2 * p2.x + 3 * t ** 2 * (1 - t) * p3.x + t ** 3 * p4.x;
			y = (1 - t) ** 3 * p1.y + 3 * t * (1 - t) ** 2 * p2.y + 3 * t ** 2 * (1 - t) * p3.y + t ** 3 * p4.y;
			color = Math.round(0xFF * t).toString(16);
			color = "#" + color + color + color;
			Path.lineTo(x, y, curTime += stepSize, prevX, prevY, color);
			prevX = x;
			prevY = y;
		}
	},
	animateCustom: function(vertices, q, duration){
		Path.canvas.moveTo(vertices[0].x, vertices[0].y);
		Path.canvas.strokeStyle = "blue";
		var stepSize = duration / q;
		var curTime = 0;
		var prevX = vertices[0].x;
		var prevY = vertices[0].y;
		var p, t;
		for(var i = 0; i <= q; i++){
			t = i / q;
			p = getBezierPoint(t, vertices);
			Path.lineTo(p.x, p.y, curTime += stepSize, prevX, prevY);
			prevX = p.x;
			prevY = p.y;
		}
	},
	lineTo: function(x, y, timeout, prevX, prevY, color){
		setTimeout(() => {
			Path.canvas.beginPath();
			Path.canvas.strokeStyle = color || "black";
			Path.canvas.moveTo(prevX, prevY);
			Path.canvas.lineTo(x, y);
			Path.canvas.stroke();
		}, timeout);
	},
	animateBSp(vert, q, dur){

	}
};

class Point {
	constructor(x, y){
		this.x = x;
		this.y = y;
	}
}
document.addEventListener("DOMContentLoaded", e => {
	Path.init();
	Path.animateCustom([
		{x: 0, y: 0},
		{x: 500, y: 0},
		{x: 500, y: 500},
		{x: 0, y: 500},
		{x: 0, y: 0},
		{x: 500, y: 0},
		{x: 500, y: 500},
		{x: 0, y: 500},
		{x: 0, y: 0},
		{x: 500, y: 0},
	], 1000, 10000);
	let c = new Canvas("canvas");
	let line = new Canvas.Shape.Line(new Canvas.Point(0,0), new Canvas.Point(100, 200));
	line.style = new Canvas.Style(new Canvas.Color.RGBa(255,0,0, 127));
	let curve = new Canvas.Shape.BezierCurve([
		{x: 0, y: 0},
		{x: 500, y: 0},
		{x: 500, y: 500},
		{x: 0, y: 500},
		{x: 0, y: 0},
		{x: 500, y: 0},
		{x: 500, y: 500},
		{x: 0, y: 500},
		{x: 0, y: 0},
		{x: 500, y: 0},
	], 100);
	c.drawShape(curve);
	c.drawShape(line);
});

function getBezierPoint(t, vertices){
	var n = vertices.length - 1;
	var x = 0, y = 0;
	var b;
	for(var k = 0; k <= n; k++){
		b = C(n, k) * Math.pow(t, k) * Math.pow(1 - t, n - k);
		x += vertices[k].x * b;
		y += vertices[k].y * b;
	}
	return new Point(x, y);

}

function fac(n){
	if(n === 0)
		return 1;
	n = Math.abs(n);
	var result = n;
	for(var i = n - 1; i > 0; i--)
		result *= i;
	return result;
}

class Canvas {

	constructor(canv){
		let c;
		if(typeof canv === "string"){
			c = document.getElementById(canv);
			if(!c)
				throw new Error("There is no canvas element with given ID");
		} else if(canv instanceof HTMLCanvasElement) {
			c = canv;
		} else if(canv instanceof CanvasRenderingContext2D) {
			c = canv.canvas;
		} else {
			throw new Error("First argument must be a string or instance of canvas element");
		}
		this.ctx = c.getContext("2d");
		this.width = c.width;
		this.height = c.height;
	}

	drawShape(shape){
		this.begin(shape);
		shape.render(this);
		this.end();
	}

	begin(shape){
		this.ctx.beginPath();
		this.ctx.strokeStyle = shape.style.stroke.toString();
		this.ctx.lineWidth = shape.style.lineWidth;
		this.ctx.stroke();
	}

	end(){
		this.ctx.stroke();
	}

	static Color = class Color {

		static COMPONENT_R = 16
		static COMPONENT_G = 8
		static COMPONENT_B = 0
		static COMPONENT_A = -1

		static RGBa = class RGBa {

			constructor(r, g, b, a = 0xFF){
				this.color = (r << 16) | (g << 8) | b;
				this.alpha = a;
			}

			getComponent(component){
				if(component === Canvas.Color.COMPONENT_A)
					return this.alpha;
				return (this.color >> component) & 0xFF;
			}

			getComponentAsString(component){
				let c = this.getComponent(component);
				if(c <= 0xF)
					return "0" + c.toString(16).toUpperCase();
				else
					return c.toString(16).toUpperCase();
			}

			toString(){
				return "#"
					+ this.getComponentAsString(Canvas.Color.COMPONENT_R)
					+ this.getComponentAsString(Canvas.Color.COMPONENT_G)
					+ this.getComponentAsString(Canvas.Color.COMPONENT_B)
					+ this.getComponentAsString(Canvas.Color.COMPONENT_A);
			}
			
			static fromString(color){
				color = color.slice(1);
				let r = +("0x" + color.slice(0, 2));
				let g = +("0x" + color.slice(2, 4));
				let b = +("0x" + color.slice(4, 6));
				let a = +("0x" + color.slice(6, 8));
				return new Canvas.Color.RGBa(r, g, b, a);
			}
		}
	}

	static Style = class Style {
		constructor(stroke = new Canvas.Color.RGBa(0, 0, 0), lineWidth = 1){
			this.stroke = stroke;
			this.lineWidth = lineWidth;
		}
	}

	static Point = class Point {
		constructor(x, y){
			this.x = x;
			this.y = y;
		}

		// getDistance(p){
		// 	return Math.sqrt((p.x - this.x) ** 2 + (p.y - this.y) ** 2);
		// }
	}

	static Shape = class Shape {

		render(canvas){throw new Error}

		static Line = class Line extends Shape {
			constructor(p1, p2, style = new Canvas.Style){
				super();
				this.p1 = p1;
				this.p2 = p2;
				this.style = style;
			}

			render(canvas){
				canvas.ctx.moveTo(this.p1.x, this.p1.y);
				canvas.ctx.lineTo(this.p2.x, this.p2.y);
			}
		}

		static Polyline = class Polyline extends Shape {
			constructor(points, style = new Canvas.Style){
				super();
				this.points = points;
				this.style = style;
			}

			render(canvas){
				canvas.ctx.moveTo(this.points[0].x, this.points[0].y);
				for(var i = 1; i < this.points.length; i++)
					canvas.ctx.lineTo(this.points[i].x, this.points[i].y);
			}
		}

		static BezierCurve = class BezierCurve extends Shape {

			constructor(points, q, style = new Canvas.Style){
				super();
				this.points = points;
				this.q = q;
				this.style = style;
			}

			render(canvas){
				canvas.ctx.moveTo(this.points[0].x, this.points[0].y);
				var p, t;
				for(var i = 0; i <= this.q; i++){
					t = i / this.q;
					p = this.getPointByTimeline(t);
					canvas.ctx.lineTo(p.x, p.y);
				}
			}

			getPointByTimeline(t){
				var n = this.points.length - 1;
				var x = 0, y = 0;
				var b;
				for(var k = 0; k <= n; k++){
					b = C(n, k) * Math.pow(t, k) * Math.pow(1 - t, n - k);
					x += this.points[k].x * b;
					y += this.points[k].y * b;
				}
				return new Canvas.Point(x, y);
			}
		}


		// Circle, Bezier, BSpline, Path, NURBS,Rect
	}

	static Animation = class Animation {
		
	}
}

function C(n, k){
	return fac(n) / (fac(n - k) * fac(k));
}
