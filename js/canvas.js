var Path = {
	canvas: null,
	dimens: null,

	init: function(){
		var canvas = document.getElementById("canvas");
		Path.canvas = canvas.getContext("2d");
		Path.dimens = {
			width: canvas.width,
			height: canvas.height
		};
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
};

class Point {
	constructor(x, y){
		this.x = x;
		this.y = y;
	}
}
document.addEventListener("DOMContentLoaded", e => {
	Path.init();
	// Path.animateCustom([
	// 	{x: 0, y: 0},
	// 	{x: 500, y: 0},
	// 	{x: 500, y: 500},
	// 	{x: 0, y: 500},
	// 	{x: 0, y: 0},
	// 	{x: 500, y: 0},
	// 	{x: 500, y: 500},
	// 	{x: 0, y: 500},
	// 	{x: 0, y: 0},
	// 	{x: 500, y: 0},
	// ], 1000, 10000);
	CanvasTest.start();
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
	let result = 1;
	while(n > 0)
		result *= n--;
	return result;
}

function C(n, k){
	return fac(n) / (fac(n - k) * fac(k));
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
			throw new Error("First argument must be a string or instance of canvas element or instance of context class");
		}
		this.ctx = c.getContext("2d");
		this.width = c.width;
		this.height = c.height;
		this.shapes = [];
		this.maxZ = -Infinity;
	}

	addShape(shape){
		if(!(shape instanceof Canvas.Shape))
			return;
		this.shapes.push(shape);
		if(shape.z === undefined)
			if(this.maxZ === -Infinity)
				shape.z = this.maxZ = 0;
			else
				shape.z = this.maxZ = this.maxZ + 1;
		else
			if(shape.z > this.maxZ)
				this.maxZ = shape.z;
	}

	removeShape(shape){
		for(let i in this.shapes)
			if(shape === this.shapes[i])
				return delete this.shapes[i];
	}

	renderShape(shape){
		this.begin(shape);
		shape.render(this);
		this.end();
	}

	render(){
		this.clear();
		for(var shape of this.shapes)
			this.renderShape(shape);
	}

	reorder(){
		this.shapes = this.shapes.sort((a, b) => {
			return a.z > b.z ? 1 : -1;
		});
		this.maxZ = this.shapes[this.shapes.length - 1].z;
	}

	begin(shape){
		this.ctx.beginPath();
		this.ctx.strokeStyle = shape.style.stroke.toString();
		this.ctx.lineWidth = shape.style.lineWidth;
		this.ctx.fillStyle = shape.style.fill.toString();
		this.ctx.lineCap = shape.style.lineCap;
		this.ctx.lineJoin = shape.style.lineJoin;
		this.ctx.setLineDash(shape.style.lineDash);
	}

	end(){
		this.ctx.fill();
		this.ctx.stroke();
	}

	clear(){
		this.ctx.clearRect(0, 0, this.width, this.height);
	}

	static Color = class Color {

		static RGBa = class RGBa {

			static COMPONENT_R = 16
			static COMPONENT_G = 8
			static COMPONENT_B = 0
			static COMPONENT_A = -1

			constructor(r, g, b, a = 0xFF){
				this.color = (r << 16) | (g << 8) | b;
				this.alpha = a;
			}

			getComponent(component){
				if(component === Canvas.Color.RGBa.COMPONENT_A)
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
					+ this.getComponentAsString(Canvas.Color.RGBa.COMPONENT_R)
					+ this.getComponentAsString(Canvas.Color.RGBa.COMPONENT_G)
					+ this.getComponentAsString(Canvas.Color.RGBa.COMPONENT_B)
					+ this.getComponentAsString(Canvas.Color.RGBa.COMPONENT_A);
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

		static HSLa = class HSLa {
			
			constructor(h, s, l, a = 1){
				this.hue = h;
				this.saturation = s;
				this.lightness = l;
				this.alpha = a;
			}

			toString(){
				return "hsla(" + this.hue + "," + this.saturation + "%," + this.lightness + "%," + this.alpha + ")";
			}
		}
	}

	static Style = class Style {

		static STROKE_BUTT = "butt";
		static STROKE_ROUND = "round";
		static STROKE_SQUARE = "square";
		static STROKE_BEVEL = "bevel";
		static STROKE_MITER = "miter";
		
		constructor(stroke = new Canvas.Color.RGBa(0, 0, 0), fill = new Canvas.Color.RGBa(0, 0, 0, 0), lineWidth = 1, lineCap = Canvas.Style.STROKE_BUTT, lineJoin = Canvas.Style.STROKE_MITER, lineDash = []){
			this.stroke = stroke;
			this.fill = fill;
			this.lineWidth = lineWidth;
			this.lineCap = lineCap;
			this.lineJoin = lineJoin;
			this.lineDash = lineDash;
		}
	}

	static Point = class Point {
		constructor(x, y){
			this.x = x;
			this.y = y;
		}

		equals(p){
			return this.x === p.x && this.y === p.y;
		}

		toString(){
			return `${this.x},${this.y}`;
		}
	}

	static Shape = class Shape {
		render(canvas){throw new Error("Attempt to call abstract method")}
		// clone(){throw new Error("Attempt to call abstract method")}
		// getPoints(){throw new Error("Attempt to call abstract method")}
		// on(event, f){throw new Error("Attempt to call abstract method")}
		// isPointInside(p){throw new Error("Attempt to call abstract method")}
		// setTransform(transform){throw new Error("Attempt to call abstract method")}
		getBoundingRect(){throw new Error}

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

			getBoundingRect(){
				var minX,
					minY,
					maxX,
					maxY;
				if(this.p1.x < this.p2.x){
					minX = this.p1.x;
					maxX = this.p2.x;
				} else {
					minX = this.p2.x;
					maxX = this.p1.x;
				}
				if(this.p1.y < this.p2.y){
					minY = this.p1.y;
					maxY = this.p2.y;
				} else {
					minY = this.p2.y;
					maxY = this.p1.y;
				}
				var width = maxX - minX;
				var height = maxY - minY;
				return new Canvas.Shape.Rect(new Point(minX, minY), width, height);
			}
		}

		static Polyline = class Polyline extends Shape {
			constructor(points = [], style = new Canvas.Style){
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

		static Rect = class Rect extends Shape {
			constructor(p, width, height, style = new Canvas.Style){
				super();
				this.p = p;
				this.width = width;
				this.height = height;
				this.style = style;
			}

			render(canvas){
				canvas.ctx.moveTo(this.p.x, this.p.y);
				canvas.ctx.lineTo(this.p.x + this.width, this.p.y);
				canvas.ctx.lineTo(this.p.x + this.width, this.p.y + this.height);
				canvas.ctx.lineTo(this.p.x, this.p.y + this.height);
				canvas.ctx.closePath();
			}
		}
		// TODO Сделать возможность рисования дуги
		static Ellipse = class Ellipse extends Shape {
			constructor(center, width, height, q, style = new Canvas.Style){
				super();
				this.center = center;
				this.width = width;
				this.height = height;
				this.q = q;
				this.style = style;
			}

			get width(){
				return this.a * 2;
			}

			set width(value){
				this.a = value / 2;
			}

			get height(){
				return this.b * 2;
			}

			set height(value){
				this.b = value / 2;
			}

			render(canvas){
				var full = Math.PI * 2;
				var ab = this.a * this.b;
				var a2 = this.a ** 2;
				var b2 = this.b ** 2;
				var p, angle;
				canvas.ctx.moveTo(this.center.x + this.a, this.center.y);
				for(let i = 0; i < this.q; i++){
					angle = full * (i / this.q);
					let r = ab / Math.sqrt(b2 * (Math.cos(angle) ** 2) + a2 * (Math.sin(angle) ** 2));
					p = new Canvas.Point(this.center.x + r * Math.cos(angle), this.center.y + r * Math.sin(angle));
					canvas.ctx.lineTo(p.x, p.y);
				}
				canvas.ctx.closePath();
			}
		}

		// TODO add from/to points in degrees
		static Circle = class Circle extends Shape {
			constructor(center, r, q, style = new Canvas.Style){
				super();
				this.center = center;
				this.r = r;
				this.q = q;
				this.style = style;
			}

			render(canvas){
				var p, angle;
				var full = Math.PI * 2;
				canvas.ctx.moveTo(this.center.x + this.r, this.center.y);
				for(let i = 0; i < this.q; i++){
					angle = full * (i / this.q);
					p = new Canvas.Point(this.center.x + this.r * Math.cos(angle), this.center.y + this.r * Math.sin(angle));
					canvas.ctx.lineTo(p.x, p.y);
				}
				canvas.ctx.closePath();
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
				var p;
				for(let i = 0; i <= this.q; i++){
					p = this.getPoint(i / this.q);
					canvas.ctx.lineTo(p.x, p.y);
				}
			}

			// Common bezier curve equation formula
			getPoint(t){
				var n = this.points.length - 1;
				var x = 0, y = 0;
				var b;
				var diff = 1 - t;
				for(var k = 0; k <= n; k++){
					b = C(n, k) * Math.pow(t, k) * Math.pow(diff, n - k);
					x += this.points[k].x * b;
					y += this.points[k].y * b;
				}
				return new Canvas.Point(x, y);
			}
		}

		static Path = class Path extends Shape {

			static Command = class Command {
				constructor(cmd, points, abs = true){
					this.cmd = cmd;
					this.points = points;
					this.abs = abs;
				}
			}

			static C_OUT = 0;
			static C_MOVE_TO = 1;
			static C_LINE_TO = 2;
			static C_CUBIC_BEZIER = 3;
			static C_QUADRATIC_BEZIER = 4;
			static C_ARC = 5;
			static C_CLOSE = 6;
			static C_LINE_H = 7;
			static C_LINE_V = 8;
			static C_START = 9;
			
			constructor(data, style = new Canvas.Style){
				super();
				this.style = style;
				if(typeof data === "string"){
					this.path = [];
					this.cursor = Canvas.Shape.Path.C_START;
					this.prevP = new Canvas.Point(0, 0);
					this.isAbs = true;
					this.currentPoints = [];
					this.parse(data);
				} else {
					this.path = data;
					// this.cursor = 
					// this.prevP = 
					this.isAbs = data[data.length - 1].cmd.toLowerCase() !== data[data.length - 1].cmd;
					// this.
				}
			}

			parse(data){
				for(let [p, c] of Object.entries(data)){
					switch(this.cursor){
						case Canvas.Shape.Path.C_START:
							this._checkStart(p, c);
							break;
						case Canvas.Shape.Path.C_OUT:

							break;
						case Canvas.Shape.Path.C_MOVE_TO:
							this._checkMoveTo(p, c);
							break;
						case Canvas.Shape.Path.C_LINE_TO:

							break;
						case Canvas.Shape.Path.C_CUBIC_BEZIER:

							break;
						case Canvas.Shape.Path.C_QUADRATIC_BEZIER:

							break;
						case Canvas.Shape.Path.C_ARC:

							break;
						case Canvas.Shape.Path.C_CLOSE:

							break;
					}
				}
			}

			_checkStart(p, c){
				if(Canvas.Shape.Path.isWhitespace(c))
					return;
				this.isAbs = c.toLowerCase() !== c;
				c = c.toLowerCase();
				if(c !== 'm')
					throw new Error(`Expected moveto command at position ${p}`);
				this.cursor = Canvas.Shape.Path.C_MOVE_TO;
			}
			
			_checkMoveTo(p, c){

			}

			_guessType(c){
				this.isAbs = c.toLowerCase() !== c;
				switch(c.toUpperCase()){
					case 'M':
						this.cursor = Canvas.Shape.Path.C_MOVE_TO;
						break;
					case 'L':
						this.cursor = Canvas.Shape.Path.C_LINE_TO;
						break;
					case 'H':
						this.cursor = Canvas.Shape.Path.C_LINE_H;
						break;
					case 'V':
						this.cursor = Canvas.Shape.Path.C_LINE_V;
						break;
					// 'QCSTZ'...
					default:
						throw new Error(`Unknown path command type '${c}'`);
				}
			}

			render(canvas){
				for(let cmd of this.path);
			}

			toString(){
				var result = "";
				for(let cmd of this.path)
					result += cmd.cmd + cmd.points.join(",");
				return result;
			}

			static isWhitespace(c){
				return c === ' ' || c === '\t' || c === '\n' || c === '\r' || c === '\f' || c === '\v';
			}
		}
		// BSpline, Path, NURBS
	}

	// static Animation = class Animation {}
	// static Event = class Event {}
	// static Layer = class Layer {}
	// static Transform = class Transform {}
}

function printHierarchy(obj, depth, tab = 1){
	if(depth < 1)
		return;
	for(let prop in obj){
		console.log("\t".repeat(tab) + prop);
		printHierarchy(obj[prop], depth - 1, tab + 1);
	}
}

var CanvasTest = {
	start: function(){
		c = new Canvas("canvas");
		var points = [
			new Point(10, 690),
			new Point(10, 10),
			new Point(300, 500),
			new Point(800, 100),
		];
		let curve = new Canvas.Shape.BezierCurve(points, 100);
		let poly = new Canvas.Shape.Polyline(points);
		let path = new Canvas.Shape.Path([
			new Canvas.Shape.Path.Command('M', [new Canvas.Point(0, 0)]),
			new Canvas.Shape.Path.Command('L', [new Canvas.Point(20, 30)]),
			new Canvas.Shape.Path.Command('l', [new Canvas.Point(20, -10)]),
		]);
		console.log(path.toString());
		let ellipse = new Canvas.Shape.Ellipse(new Point(c.width / 2, c.height / 2), c.width, c.height, 360);

		poly.style.stroke = new Canvas.Color.RGBa(0, 0, 0, 0x80);
		poly.style.lineWidth = 2;
		poly.style.lineDash = [10, 10];
		poly.style.lineCap = Canvas.Style.STROKE_ROUND;
		c.addShape(curve);
		c.addShape(poly);
		c.addShape(ellipse);
		curve.style.lineWidth = 3;
		curve.style.lineCap = "round";
		c.render();
	},
};
