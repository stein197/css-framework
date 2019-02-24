import java.text.ParseException;
import java.util.ArrayList;

/**
 * 
 */
public class BracketParser{

	public static final byte BRACE_ROUND = 0b0001;
	public static final byte BRACE_SQUARE = 0b0010;
	public static final byte BRACE_CORNER = 0b0100;
	public static final byte BRACE_CURVE = 0b1000;

	public final String raw;
	public BracePair brace;

	private byte depth;

	public BracketParser(String data, byte brace, byte depth) throws ParseException{
		this.raw = data;
		this.brace = new BracePair(brace);
		this.setDepth(depth);
	}

	public BracketParser(String data, byte depth){
		this.raw = data;
		this.setDepth(depth);
	}

	/**
	 * Устанавливает глубину выводимых скобок
	 * @param depth Устанавливаемая глубина. Если она меньше 1, то выставляется 1
	 */
	public void setDepth(byte depth){
		if(depth < 1)
			this.depth = 1;
		else
			this.depth = depth;
	}

	/**
	 * Парсит строку {@code data} и выделяет в ней подстроки в скобках глубины {@code depth}.
	 * @return Массив найденых подстрок в скобках указанной глубины 
	 * @throws ParseException Если в исходной строке оказалось слишком много открывающих/закрывающих скобок
	 * @see #checkDepth(byte)
	 */
	public ArrayList<String> parse() throws ParseException{
		ArrayList<String> list = new ArrayList<>();
		byte depth = 0;
		StringBuilder current = null;
		for(char c : this.raw.toCharArray()){
			if(c == this.brace.start && ++depth == this.depth){
				current = new StringBuilder();
				continue;
			} else if(c == this.brace.end){
				depth--;
			}
			if(depth >= this.depth){
				current.append(c);
			} else {
				if(current != null){
					list.add(current.toString());
					current = null;
				}
			}
		}
		checkDepth(depth);
		return list;
	}

	/**
	 * Проверяет на равенство глубины скобок нулю. Используется только внутри метода {@link #parse()}
	 * @param depth Проверяемое значение глубины. Всегда должно быть равно нулю
	 * @throws ParseException Если переданное значение меньше или больше нуля. Если значение не равно нулю,
	 * то открывающих или закрывающих скобок в строке больше чем ожидается
	 */
	private static void checkDepth(byte depth) throws ParseException{
		if(depth < 0)
			throw new ParseException("Too many closing braces", -1);
		if(depth > 0)
			throw new ParseException("Too many opening braces", 1);
	}

	/**
	 * Представляет собой пары открывающих/закрывающих скобок.
	 * Имеет только два константных поля - {@code start} и {@code end}
	 */
	public static class BracePair{

		public final char start;
		public final char end;

		public BracePair(byte brace) throws ParseException{
			switch(brace){
				case BracketParser.BRACE_ROUND:
					this.start = '(';
					this.end = ')';
					break;
				case BracketParser.BRACE_SQUARE:
					this.start = '[';
					this.end = ']';
					break;
				case BracketParser.BRACE_CORNER:
					this.start = '<';
					this.end = '>';
					break;
				case BracketParser.BRACE_CURVE:
					this.start = '{';
					this.end = '}';
					break;
				default:
					throw new ParseException("There is no brace with given code", 0);
			}
		}
	}
}