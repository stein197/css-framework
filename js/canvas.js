document.addEventListener("DOMContentLoaded", e => {
	CanvasTest.start();
});

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
	/**
	 * Создаёт объект холста
	 * @param {(string|HTMLCanvasElement|CanvasRenderingContext2D)} canv Объект канваса. Строка представляющая ID холста или сам объект холста
	 */
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

	/**
	 * Добавляет фигуру во внутренний список.
	 * Внутренний список сортируется в зависимости от z-параметров фигур.
	 * Если у фигуры нет z-параметра, то фигура располагается в самом верху списка
	 * @param {Canvas.Shape} shape Фигура
	 * @return {void}
	 */
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

	/**
	 * Удаляет фигуру из списка фигур.
	 * @param {Canvas.Shape} shape Удаляемая фигура. При этом
	 *                             происходит удаление по ссылке.
	 * @return {boolean} {@code true} если фигура была успешно удалена
	 */
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
			
			constructor(data, style = new Canvas.Style){
				super();
				this.style = style;
				this.prevP = new Canvas.Point(0, 0);
				this.currentDigit = "";
				if(typeof data === "string"){
					this.path = [];
					this.parse(data);
				} else {
					this.path = data;
				}
			}

			parse(data){
				for(let [p, c] of Object.entries(data))
					this._checkCommand(p, c);
				this._writeCommand();
			}
			
			_checkCommand(p, c){
				switch(true){
					case Canvas.Shape.Path.isDigit(c):
						this.currentDigit += c;
						break;
					case Canvas.Shape.Path.isWhitespace(c) || c === ',':
						if(this.currentDigit)
							this._writeDigit();
						break;
					case Canvas.Shape.Path.isCommand(c):
						if(this.currentCommand)
							this._writeCommand();
						this.currentCommand = new Canvas.Shape.Path.Command(c);
						break;
					default:
						throw new Error(`Unknown command '${c}' at position ${p}`);
				}
			}

			_writeDigit(c){
				if(!this.currentDigit)
					return;
				this.currentCommand.points.push(parseFloat(this.currentDigit));
				this.currentDigit = "";
			}

			_writeCommand(){
				this._writeDigit();
				this.path.push(this.currentCommand);
			}

			_M(cmd, canvas){
				if(cmd.isAbs)
					canvas.ctx.moveTo(this.prevP.x = cmd.points[0], this.prevP.y = cmd.points[1]);
				else
					canvas.ctx.moveTo(this.prevP.x += cmd.points[0], this.prevP.y += cmd.points[1]);
			}

			_L(cmd, canvas){
				if(cmd.isAbs)
					for(let i = 0; i < cmd.points.length; i += 2)
						canvas.ctx.lineTo(this.prevP.x = cmd.points[i], this.prevP.y = cmd.points[i + 1]);
				else
					for(let i = 0; i < cmd.points.length; i += 2)
						canvas.ctx.lineTo(this.prevP.x += cmd.points[i], this.prevP.y += cmd.points[i + 1]);
			}

			_H(cmd, canvas){
				if(cmd.isAbs)
					for(let i = 0; i < cmd.points.length; i++)
						canvas.ctx.lineTo(this.prevP.x = cmd.points[i], this.prevP.y);
				else
					for(let i = 0; i < cmd.points.length; i++)
						canvas.ctx.lineTo(this.prevP.x += cmd.points[i], this.prevP.y);
			}

			_V(cmd, canvas){
				if(cmd.isAbs)
					for(let i = 0; i < cmd.points.length; i++)
						canvas.ctx.lineTo(this.prevP.x, this.prevP.y = cmd.points[i]);
				else
					for(let i = 0; i < cmd.points.length; i++)
						canvas.ctx.lineTo(this.prevP.x, this.prevP.y += cmd.points[i]);
			}

			_C(cmd, canvas){
				if(cmd.isAbs)
					for(let i = 0; i < cmd.points.length; i += 6)
						canvas.ctx.bezierCurveTo(
							cmd.points[i],
							cmd.points[i + 1],
							cmd.points[i + 2],
							cmd.points[i + 3],
							this.prevP.x = cmd.points[i + 4],
							this.prevP.y = cmd.points[i + 5]
						);
				else
					for(let i = 0; i < cmd.points.length; i += 6)
						canvas.ctx.bezierCurveTo(
							this.prevP.x + cmd.points[i],
							this.prevP.y + cmd.points[i + 1],
							this.prevP.x + cmd.points[i + 2],
							this.prevP.y + cmd.points[i + 3],
							this.prevP.x += cmd.points[i + 4],
							this.prevP.y += cmd.points[i + 5]
						);
			}

			_Q(cmd, canvas){
				if(cmd.isAbs)
					for(let i = 0; i < cmd.points.length; i += 4)
						canvas.ctx.quadraticCurveTo(
							cmd.points[i],
							cmd.points[i + 1],
							this.prevP.x = cmd.points[i + 2],
							this.prevP.y = cmd.points[i + 3]
						);
				else
					for(let i = 0; i < cmd.points.length; i += 4)
						canvas.ctx.quadraticCurveTo(
							this.prevP.x + cmd.points[i],
							this.prevP.y + cmd.points[i + 1],
							this.prevP.x += cmd.points[i + 2],
							this.prevP.y += cmd.points[i + 3]
						);
			}

			_Z(cmd, canvas){
				canvas.ctx.closePath();
			}

			render(canvas){
				for(let cmd of this.path)
					this['_' + cmd.cmd.toUpperCase()](cmd, canvas);
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

			static isDigit(c){
				var charCode = c.charCodeAt();
				return (48 <= charCode && charCode <= 57) || c === '.' || c === '-';
			}

			static isCommand(c){
				return "MLHVCSQTAZ".indexOf(c.toUpperCase()) >= 0;
			}

			static Command = class Command {
				constructor(cmd, points = []){
					this.cmd = cmd;
					this.points = points;
					this.isAbs = cmd.toUpperCase() === cmd;
				}
			}
		}
		// BSpline, NURBS
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
			new Canvas.Point(10, 690),
			new Canvas.Point(10, 10),
			new Canvas.Point(300, 500),
			new Canvas.Point(800, 100),
		];
		let curve = new Canvas.Shape.BezierCurve(points, 100);
		let poly = new Canvas.Shape.Polyline(points);
		let ellipse = new Canvas.Shape.Ellipse(new Canvas.Point(c.width / 2, c.height / 2), c.width, c.height, 360);
		var pdata = "M100,100C0,100,200,100,200,0l10,10 q 0 100 100 100";
		var path = new Canvas.Shape.Path(pdata);
		c.addShape(path);
		console.log(path, pdata, path.toString());

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
