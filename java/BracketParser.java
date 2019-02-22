import java.text.ParseException;

public class BracketParser{

	public static final byte BRACE_ROUND = 0b0001;
	public static final byte BRACE_SQUARE = 0b0010;
	public static final byte BRACE_CORNER = 0b0100;
	public static final byte BRACE_CURVE = 0b1000;

	public boolean includeInner = true;
	public final String raw;
	public BracePair brace;

	private byte depth;

	public BracketParser(String data, byte brace, byte depth) throws ParseException{
		this.raw = data;
		this.brace = new BracePair(brace);
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

	public void parse() throws ParseException{
		byte depth = 0;
		for(char c : this.raw.toCharArray()){
			if(c == this.brace.start)
				depth++;
			else if(c == this.brace.end)
				depth--;
		}
		checkDepth(depth);
	}

	private static void checkDepth(byte depth) throws ParseException{
		if(depth < 0)
			throw new ParseException("Too many closing braces", -1);
		if(depth > 0)
			throw new ParseException("Too many opening braces", 1);
	}

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
					throw new ParseException("There is no brace with given code");
			}
		}
	}
}